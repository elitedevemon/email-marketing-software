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
use Throwable;

class SendOutboundEmailJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public function __construct(public int $outboundId)
  {
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