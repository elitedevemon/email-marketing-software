<?php

namespace App\Services;

use App\Jobs\SendOutboundEmailJob;
use App\Models\Client;
use App\Models\EmailOutbound;
use App\Models\Sender;
use App\Models\Sequence;
use App\Models\SequenceEnrollment;
use App\Models\SequenceStep;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class SequenceEngine
{
  public function enrollDefaultForClient(Client $client): ?SequenceEnrollment
  {
    // Only prospect clients are auto-enrolled (suppressed/engaged/paused/archived are excluded)
    if (($client->status ?? '') !== 'prospect')
      return null;

    $key = (string) env('DEFAULT_SEQUENCE_KEY', 'default_outreach');
    $seq = Sequence::query()->where('key', $key)->where('is_active', true)->first();
    if (!$seq)
      return null;

    // prevent double enrollment for same client+sequence
    $existing = SequenceEnrollment::query()
      ->where('client_id', $client->id)
      ->where('sequence_id', $seq->id)
      ->whereIn('status', ['active', 'completed'])
      ->first();
    if ($existing)
      return $existing;

    $enrollment = SequenceEnrollment::create([
      'client_id' => $client->id,
      'sequence_id' => $seq->id,
      'status' => 'active',
      'current_step_order' => 1,
      'next_run_at' => now(),
      'started_at' => now(),
    ]);

    // try to queue immediately (matches "Email#1 instantly after client add")
    $this->queueForEnrollment($enrollment, dryRun: false);

    return $enrollment;
  }

  public function queueDueEnrollments(int $limit = 200, bool $dryRun = false): array
  {
    $due = SequenceEnrollment::query()
      ->where('status', 'active')
      ->whereNotNull('next_run_at')
      ->where('next_run_at', '<=', now())
      ->orderBy('next_run_at')
      ->limit($limit)
      ->get();

    $m = ['due' => $due->count(), 'queued' => 0, 'pending' => 0, 'skipped' => 0];
    foreach ($due as $enr) {
      $r = $this->queueForEnrollment($enr, $dryRun);
      $m['queued'] += $r['queued'];
      $m['pending'] += $r['pending'];
      $m['skipped'] += $r['skipped'];
    }
    return $m;
  }

  public function queueForEnrollment(SequenceEnrollment $enrollment, bool $dryRun = false): array
  {
    return DB::transaction(function () use ($enrollment, $dryRun) {
      $enr = SequenceEnrollment::query()->whereKey($enrollment->id)->lockForUpdate()->first();
      if (!$enr || $enr->status !== 'active') {
        return ['queued' => 0, 'pending' => 0, 'skipped' => 1];
      }

      if (!$enr->next_run_at || $enr->next_run_at->gt(now())) {
        return ['queued' => 0, 'pending' => 0, 'skipped' => 1];
      }

      $step = SequenceStep::query()
        ->where('sequence_id', $enr->sequence_id)
        ->where('step_order', $enr->current_step_order)
        ->where('is_active', true)
        ->first();

      if (!$step) {
        // no more steps => complete
        $enr->status = 'completed';
        $enr->next_run_at = null;
        $enr->save();
        return ['queued' => 0, 'pending' => 0, 'skipped' => 0];
      }

      // ensure outbound exists (idempotent via unique index)
      $outbound = EmailOutbound::query()
        ->where('sequence_enrollment_id', $enr->id)
        ->where('sequence_step_id', $step->id)
        ->lockForUpdate()
        ->first();

      if (!$outbound) {
        try {
          $outbound = EmailOutbound::create([
            'client_id' => $enr->client_id,
            'sequence_enrollment_id' => $enr->id,
            'sequence_step_id' => $step->id,
            'subject' => $step->subject,
            'body_html' => $step->body_html,
            'body_text' => $step->body_text,
            'status' => 'pending',
            'scheduled_at' => now(),
            'attempts' => 0,
          ]);
        } catch (QueryException $e) {
          // duplicate created by another runner; fetch it
          $outbound = EmailOutbound::query()
            ->where('sequence_enrollment_id', $enr->id)
            ->where('sequence_step_id', $step->id)
            ->lockForUpdate()
            ->first();
        }
      }

      // if already queued/sent, avoid re-queuing; wait for job to advance enrollment
      if (in_array($outbound->status, ['queued', 'sending', 'sent'], true)) {
        $enr->next_run_at = now()->addMinutes(10);
        $enr->save();
        return ['queued' => 0, 'pending' => 0, 'skipped' => 1];
      }

      // pick sender
      $client = Client::query()->find($enr->client_id);
      if (!$client || ($client->status ?? '') !== 'prospect') {
        $enr->status = 'stopped';
        $enr->stopped_at = now();
        $enr->stop_reason = 'client_not_sendable';
        $enr->next_run_at = null;
        $enr->save();
        return ['queued' => 0, 'pending' => 0, 'skipped' => 0];
      }

      $sender = $this->pickSender();
      if (!$sender) {
        // backoff (prevents hammering every minute)
        $enr->next_run_at = now()->addMinutes(5);
        $enr->save();
        return ['queued' => 0, 'pending' => 1, 'skipped' => 0];
      }

      if ($dryRun) {
        return ['queued' => 0, 'pending' => 0, 'skipped' => 0];
      }

      // queue outbound
      $outbound->sender_id = $sender->id;
      $outbound->status = 'queued';
      $outbound->queued_at = now();
      $outbound->save();

      $delay = $this->randomJitterSeconds($sender);
      SendOutboundEmailJob::dispatch($outbound->id)->delay(now()->addSeconds($delay));

      // short backoff while job runs; job will set next_run_at after success/failure
      $enr->next_run_at = now()->addMinutes(10);
      $enr->save();

      return ['queued' => 1, 'pending' => 0, 'skipped' => 0];
    });
  }

  private function pickSender(): ?Sender
  {
    // pick least-used active sender first
    $candidates = Sender::query()
      ->where('is_active', true)
      ->orderBy('sent_today')
      ->limit(25)
      ->get();

    foreach ($candidates as $s) {
      if ($this->senderHasCapacityNow($s)) {
        return $s;
      }
    }
    return null;
  }

  private function senderHasCapacityNow(Sender $s): bool
  {
    $tz = $s->timezone ?: 'Asia/Dhaka';
    $now = Carbon::now($tz);

    // reset daily counter if date changed (in sender timezone)
    $today = $now->toDateString();
    $sentToday = ($s->sent_today_date && $s->sent_today_date->toDateString() === $today) ? (int) $s->sent_today : 0;
    if ($sentToday >= (int) $s->daily_limit)
      return false;

    $start = (string) $s->window_start;
    $end = (string) $s->window_end;
    if ($start === '' || $end === '')
      return true;

    $t = $now->format('H:i:s');

    // handle overnight windows (e.g. 22:00–06:00)
    if ($start <= $end) {
      return $t >= $start && $t <= $end;
    }
    return ($t >= $start) || ($t <= $end);
  }

  private function randomJitterSeconds(Sender $s): int
  {
    $min = max(0, (int) $s->jitter_min_seconds);
    $max = max(0, (int) $s->jitter_max_seconds);
    if ($max < $min)
      [$min, $max] = [$max, $min];
    return $max === $min ? $min : random_int($min, $max);
  }
}