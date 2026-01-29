<?php

namespace App\Services\Sending;

use App\Models\EmailSendLog;
use Throwable;

class SendLogService
{
  public function success(array $ctx, int $attempt, ?int $durationMs = null, array $meta = []): void
  {
    $this->write($ctx, 'success', $attempt, $durationMs, null, $meta);
  }

  public function skipped(array $ctx, int $attempt, string $reason, array $meta = []): void
  {
    $meta = array_merge($meta, ['skip_reason' => $reason]);
    $this->write($ctx, 'skipped', $attempt, null, null, $meta);
  }

  public function failed(array $ctx, int $attempt, Throwable $e, ?int $durationMs = null, array $meta = []): void
  {
    $this->write($ctx, 'failed', $attempt, $durationMs, $e, $meta);
  }

  private function write(array $ctx, string $status, int $attempt, ?int $durationMs, ?Throwable $e, array $meta): void
  {
    EmailSendLog::query()->create([
      'email_outbound_id' => $ctx['email_outbound_id'] ?? null,
      'outbound_uuid' => $ctx['outbound_uuid'] ?? null,
      'client_id' => $ctx['client_id'] ?? null,
      'sender_id' => $ctx['sender_id'] ?? null,
      'to_email' => $ctx['to_email'] ?? null,
      'subject' => $ctx['subject'] ?? null,
      'status' => $status,
      'attempt' => max(1, $attempt),
      'duration_ms' => $durationMs,
      'error_class' => $e ? get_class($e) : null,
      'error_message' => $e ? substr((string) $e->getMessage(), 0, 2000) : null,
      'meta_json' => $meta ?: null,
    ]);
  }
}