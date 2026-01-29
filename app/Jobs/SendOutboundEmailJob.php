<?php

namespace App\Jobs;

use App\Mail\OutboundMailable;
use App\Models\Client;
use App\Models\EmailOutbound;
use App\Models\Sender;
use App\Models\SequenceEnrollment;
use App\Models\SequenceStep;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Services\SuppressionService;
use App\Services\Tracking\OutboundTrackingService;
use App\Models\SenderDailyCounter;
use App\Services\Sending\DomainClassifier;
use App\Services\Sending\DomainThrottleService;
use App\Services\Sending\SenderSelectionService;
use App\Services\Sending\SendingWindowService;
use App\Services\Sending\SendLogService;
use Illuminate\Support\Str;
use Throwable;

class SendOutboundEmailJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public function __construct(public int $outboundId)
  {
  }

  public function backoff(): array
  {
    // Retry schedule (seconds) - keeps bursts under control
    return [30, 90, 180, 300, 600];
  }

  public function handle(): void
  {
    if (!filter_var(env('EMAIL_SENDING_ENABLED', 'true'), FILTER_VALIDATE_BOOLEAN)) {
      // keep as pending for later enable
      return;
    }

    DB::transaction(function () {
      /** @var EmailOutbound|null $o */
      $o = EmailOutbound::query()->whereKey($this->outboundId)->lockForUpdate()->first();
      if (!$o || $o->status !== 'queued')
        return;

      $client = Client::query()->find($o->client_id);
      if (!$client || ($client->status ?? '') !== 'prospect') {
        $o->status = 'cancelled';
        $o->last_error = 'client_not_sendable';
        $o->save();
        return;
      }

      $sender = Sender::query()->whereKey($o->sender_id)->lockForUpdate()->first();
      if (!$sender || !$sender->is_active) {
        $o->status = 'pending';
        $o->sender_id = null;
        $o->last_error = 'sender_unavailable';
        $o->save();
        return;
      }

      // Global suppression enforcement (hard stop)
      $suppression = app(SuppressionService::class);
      if ($suppression->isSuppressed($o->to_email ?? $o->client?->email)) {
        $o->status = 'skipped';
        $o->skipped_at = now();
        $o->skip_reason = 'suppressed';
        $o->save();
        return;
      }
      // Compose: add unsubscribe + tracking (HTML + text)
      $unsubscribeUrl = $suppression->makeSignedUnsubscribeUrl(
        $o->to_email ?? $o->client?->email,
        $o->client_id ?? null,
        $o->uuid ?? null
      );

      $tracking = app(OutboundTrackingService::class);
      $html = $o->html_body ?? $o->body_html ?? '';
      $text = $o->text_body ?? $o->body_text ?? '';
      if ($html)
        $html = $tracking->applyToHtml($html, $o->uuid, $unsubscribeUrl);
      if ($text)
        $text = $tracking->applyToText($text, $unsubscribeUrl);

      // persist rendered content for audit/debug
      $o->rendered_html = $html ?: null;
      $o->rendered_text = $text ?: null;
      $o->save();

      ###########
      // Determine recipient email (adjust field names as per your schema)
      $toEmail = $o->to_email ?? $o->client?->email;
      if (!$toEmail) {
        // mark failed/skip as per your existing pattern
        $o->status = 'failed';
        $o->save();
        return;
      }

      // 1) Enforce sending window (prefer sender-specific later; if sender not set yet use global)
      $windowStart = $o->sender?->window_start ?? null;
      $windowEnd = $o->sender?->window_end ?? null;
      $tz = $o->sender?->timezone ?? null;
      [$allowed, $delayToWindow] = app(SendingWindowService::class)->check($windowStart, $windowEnd, $tz);
      if (!$allowed) {
        $this->release($delayToWindow);
        return;
      }

      // 2) Apply jitter ONCE (retry-safe)
      if (empty($o->jitter_applied_at)) {
        $min = (int) ($o->sender?->jitter_min_seconds ?? config('sending.jitter.min', 15));
        $max = (int) ($o->sender?->jitter_max_seconds ?? config('sending.jitter.max', 180));
        $min = max(0, min($min, $max));
        $max = max($min, $max);
        $jitter = $max > 0 ? random_int($min, $max) : 0;

        $o->jitter_applied_at = now();
        $o->save();

        if ($jitter > 0) {
          $this->release($jitter);
          return;
        }
      }

      // 3) Sender rotation + quota enforcement (if sender missing or not eligible)
      $sender = $o->sender;
      $needsNewSender = !$sender || (bool) ($sender->is_paused ?? false);
      if ($needsNewSender) {
        $sender = app(SenderSelectionService::class)->selectEligibleSender();
        if (!$sender) {
          // no sender available now -> retry later
          $this->release(300);
          return;
        }
        $o->sender_id = $sender->id;
        $o->save();
      }

      // 4) Daily limit enforcement (hard)
      $limit = (int) ($sender->daily_limit ?? 0);
      if ($limit > 0) {
        $counter = SenderDailyCounter::query()
          ->firstOrCreate(
            ['sender_id' => $sender->id, 'date' => now()->toDateString()],
            ['sent_count' => 0],
          );
        if ((int) $counter->sent_count >= $limit) {
          // try rotate once more
          $alt = app(SenderSelectionService::class)->selectEligibleSender();
          if ($alt && $alt->id !== $sender->id) {
            $o->sender_id = $alt->id;
            $o->save();
            $this->release(5);
            return;
          }
          // wait until tomorrow (simple) - next tick will catch it
          $this->release(3600);
          return;
        }
      }

      // 5) Per-domain throttling + reservation
      $group = app(DomainClassifier::class)->groupForEmail($toEmail);
      $delay = app(DomainThrottleService::class)->reserveOrDelay($group);
      if ($delay > 0) {
        $this->release($delay);
        return;
      }
      ###########

      ###########
      $attempt = method_exists($this, 'attempts') ? $this->attempts() : 1;
      $ctx = [
        'email_outbound_id' => $o->id,
        'outbound_uuid' => $o->uuid ?? null,
        'client_id' => $o->client_id ?? null,
        'sender_id' => $o->sender_id ?? null,
        'to_email' => $o->to_email ?? ($o->client?->email),
        'subject' => $o->subject ?? null,
      ];

      // ---- Atomic sending lock (prevents double-send across workers) ----
      $lockKey = 'job:' . Str::uuid()->toString();
      $locked = DB::table('email_outbounds')
        ->where('id', $o->id)
        ->whereNull('sent_at')
        ->whereNull('skipped_at')
        ->whereIn('status', ['queued', 'pending', 'sending']) // adjust to your enum
        ->where(function ($q) {
          $q->whereNull('sending_started_at')
            ->orWhere('sending_started_at', '<', now()->subMinutes(10)); // stale lock safety
        })
        ->update([
          'status' => 'sending',
          'sending_started_at' => now(),
          'sending_lock_key' => $lockKey,
          'updated_at' => now(),
        ]);

      if ($locked !== 1) {
        // Another worker already locked/sent it.
        return;
      }

      $t0 = microtime(true);
      ###########

      // Enforce daily limit (timezone-aware reset)
      $tz = $sender->timezone ?: 'Asia/Dhaka';
      $nowTz = Carbon::now($tz);
      $today = $nowTz->toDateString();
      if (!$sender->sent_today_date || $sender->sent_today_date->toDateString() !== $today) {
        $sender->sent_today_date = $today;
        $sender->sent_today = 0;
      }
      if ($sender->sent_today >= $sender->daily_limit) {
        $o->status = 'pending';
        $o->sender_id = null;
        $o->last_error = 'daily_limit_reached';
        $o->save();
        return;
      }

      $o->status = 'sending';
      $o->attempts = (int) $o->attempts + 1;
      $o->save();

      // Set dynamic mailer config for this job/process
      config([
        'mail.default' => 'dynamic',
        'mail.mailers.dynamic' => [
          'transport' => 'smtp',
          'host' => $sender->smtp_host,
          'port' => (int) $sender->smtp_port,
          'encryption' => $sender->smtp_encryption === 'none' ? null : $sender->smtp_encryption,
          'username' => $sender->smtp_username,
          'password' => $sender->smtp_password,
          'timeout' => null,
          'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ],
        'mail.from.address' => $sender->from_email,
        'mail.from.name' => $sender->from_name,
      ]);

      try {
        Mail::mailer('dynamic')
          ->to($client->email)
          ->send(new OutboundMailable(
            subject: (string) $o->subject,
            html: (string) ($o->body_html ?? ''),
            text: (string) ($o->body_text ?? '')
          ));

        $o->status = 'sent';
        $o->sent_at = now();
        $o->last_error = null;
        $o->save();

        $sender->sent_today = (int) $sender->sent_today + 1;
        $sender->last_sent_at = now();
        $sender->save();

        $this->advanceEnrollmentAfterSend($o->sequence_enrollment_id, $o->sent_at);

        // After successful send: increment daily counter
        if ($limit > 0) {
          SenderDailyCounter::query()
            ->where('sender_id', $sender->id)
            ->where('date', now()->toDateString())
            ->increment('sent_count', 1, ['last_sent_at' => now()]);
        }

        $ms = (int) round((microtime(true) - $t0) * 1000);
        app(SendLogService::class)->success($ctx, $attempt, $ms, [
          'lock_key' => $lockKey,
        ]);
      } catch (Throwable $e) {
        // allow retry later via tick; rotate sender next time
        $o->status = 'pending';
        $o->sender_id = null;
        $o->last_error = mb_substr($e->getMessage(), 0, 1000);
        $o->save();

        $enr = SequenceEnrollment::query()->whereKey($o->sequence_enrollment_id)->lockForUpdate()->first();
        if ($enr && $enr->status === 'active') {
          $enr->next_run_at = now()->addMinutes(10); // backoff
          $enr->save();
        }

        // Provider/domain backoff (helps prevent rapid repeated failures)
        app(DomainThrottleService::class)->penalize($group, $this->attempts());

        $ms = (int) round((microtime(true) - $t0) * 1000);
        app(SendLogService::class)->failed($ctx, $attempt, $e, $ms, [
          'lock_key' => $lockKey,
        ]);

        // Release lock so retry can re-acquire (optional: only if you keep status=queued on fail)
        DB::table('email_outbounds')
          ->where('id', $o->id)
          ->where('sending_lock_key', $lockKey)
          ->update([
            'sending_started_at' => null,
            'sending_lock_key' => null,
            'status' => 'queued', // adjust to your enum
            'updated_at' => now(),
          ]);

        throw $e; // let queue retry policy apply
      }
    });
  }

  private function advanceEnrollmentAfterSend(int $enrollmentId, Carbon $sentAt): void
  {
    $enr = SequenceEnrollment::query()->whereKey($enrollmentId)->lockForUpdate()->first();
    if (!$enr || $enr->status !== 'active')
      return;

    $nextOrder = (int) $enr->current_step_order + 1;
    $nextStep = SequenceStep::query()
      ->where('sequence_id', $enr->sequence_id)
      ->where('step_order', $nextOrder)
      ->where('is_active', true)
      ->first();

    if (!$nextStep) {
      $enr->status = 'completed';
      $enr->next_run_at = null;
      $enr->save();
      return;
    }

    $enr->current_step_order = $nextOrder;
    $enr->next_run_at = $sentAt->copy()->addDays((int) $nextStep->delay_days);
    $enr->save();
  }
}