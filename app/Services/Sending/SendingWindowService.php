<?php

namespace App\Services\Sending;

use Carbon\CarbonImmutable;

class SendingWindowService
{
  /**
   * Returns: [bool $allowed, int $delaySecondsIfNotAllowed]
   */
  public function check(?string $windowStart, ?string $windowEnd, ?string $tz): array
  {
    $tz = $tz ?: config('sending.window.timezone');
    $now = CarbonImmutable::now($tz);

    $start = $windowStart ?: config('sending.window.start');
    $end = $windowEnd ?: config('sending.window.end');

    // If misconfigured, allow sending
    if (!$start || !$end)
      return [true, 0];

    $todayStart = $now->startOfDay()->addSeconds($this->hmsToSeconds($start));
    $todayEnd = $now->startOfDay()->addSeconds($this->hmsToSeconds($end));

    // Supports windows that cross midnight (e.g. 22:00–06:00)
    $allowed = $todayStart->lte($todayEnd)
      ? ($now->gte($todayStart) && $now->lte($todayEnd))
      : ($now->gte($todayStart) || $now->lte($todayEnd));

    if ($allowed)
      return [true, 0];

    // compute next open
    $nextOpen = $todayStart->lte($todayEnd)
      ? ($now->lt($todayStart) ? $todayStart : $todayStart->addDay())
      : ($now->lt($todayEnd) ? $todayEnd : $todayStart); // cross-midnight: before end means currently closed until end; else until start

    $delay = max(15, $now->diffInSeconds($nextOpen, false) ?: 60);
    return [false, $delay];
  }

  private function hmsToSeconds(string $hms): int
  {
    $parts = array_map('intval', explode(':', $hms));
    $h = $parts[0] ?? 0;
    $m = $parts[1] ?? 0;
    $s = $parts[2] ?? 0;
    return $h * 3600 + $m * 60 + $s;
  }
}