<?php

namespace App\Services\Sending;

use App\Models\Sender;
use App\Models\SenderDailyCounter;
use Illuminate\Support\Facades\DB;

class SenderSelectionService
{
  /**
   * Selects a sender eligible NOW (window + quota + not paused).
   * Round-robin via last_selected_at.
   */
  public function selectEligibleSender(): ?Sender
  {
    return DB::transaction(function () {
      // NOTE: adjust column names if your Sender model differs
      $sender = Sender::query()
        ->where('is_paused', false)
        ->orderByRaw('last_selected_at is null desc')
        ->orderBy('last_selected_at', 'asc')
        ->lockForUpdate()
        ->first();

      if (!$sender)
        return null;

      // window check (sender-specific)
      [$allowed, $delay] = app(SendingWindowService::class)
        ->check($sender->window_start ?? null, $sender->window_end ?? null, $sender->timezone ?? null);
      if (!$allowed)
        return null;

      // quota check
      $limit = (int) ($sender->daily_limit ?? 0);
      if ($limit > 0) {
        $counter = SenderDailyCounter::query()
          ->firstOrCreate(
            ['sender_id' => $sender->id, 'date' => now()->toDateString()],
            ['sent_count' => 0],
          );
        if ((int) $counter->sent_count >= $limit) {
          // move this sender to the back (so others get picked)
          $sender->last_selected_at = now();
          $sender->save();
          return null;
        }
      }

      $sender->last_selected_at = now();
      $sender->save();

      return $sender;
    }, 3);
  }
}