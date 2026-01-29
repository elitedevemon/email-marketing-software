<?php

namespace App\Services\Sending;

use App\Models\DomainThrottleState;
use Illuminate\Support\Facades\DB;

class DomainThrottleService
{
  /**
   * Returns delay seconds if throttled, otherwise 0 and reserves the next slot.
   */
  public function reserveOrDelay(string $group): int
  {
    $interval = (int) (config('sending.domain_intervals.' . $group) ?? 1);

    return DB::transaction(function () use ($group, $interval) {
      $row = DomainThrottleState::query()
        ->where('group', $group)
        ->lockForUpdate()
        ->first();

      if (!$row) {
        $row = DomainThrottleState::query()->create(['group' => $group]);
        $row->refresh();
      }

      $now = now();
      if ($row->next_available_at && $row->next_available_at->isFuture()) {
        return max(1, $now->diffInSeconds($row->next_available_at));
      }

      $row->next_available_at = $now->copy()->addSeconds(max(1, $interval));
      $row->save();

      return 0;
    }, 3);
  }

  public function penalize(string $group, int $attempts = 1): int
  {
    $base = (int) config('sending.domain_backoff_base', 30);
    $max = (int) config('sending.domain_backoff_max', 900);
    $penalty = min($max, (int) ($base * max(1, $attempts)));

    DB::transaction(function () use ($group, $penalty) {
      $row = DomainThrottleState::query()
        ->where('group', $group)
        ->lockForUpdate()
        ->first();

      if (!$row) {
        $row = DomainThrottleState::query()->create(['group' => $group]);
        $row->refresh();
      }

      $row->error_streak = min(50, (int) $row->error_streak + 1);
      $row->last_error_at = now();
      $candidate = now()->addSeconds($penalty);
      $row->next_available_at = $row->next_available_at && $row->next_available_at->gt($candidate)
        ? $row->next_available_at
        : $candidate;
      $row->save();
    }, 3);

    return $penalty;
  }
}