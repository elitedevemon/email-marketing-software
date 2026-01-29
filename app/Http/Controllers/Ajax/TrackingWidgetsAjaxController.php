<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\EmailEvent;
use App\Models\OutboundLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackingWidgetsAjaxController extends Controller
{
  public function trend(Request $request)
  {
    $days = max(7, min(90, (int) $request->query('days', 30)));

    $rows = EmailEvent::query()
      ->selectRaw('DATE(occurred_at) as d, type, COUNT(*) as c')
      ->where('occurred_at', '>=', now()->subDays($days)->startOfDay())
      ->whereIn('type', ['open', 'click'])
      ->groupBy('d', 'type')
      ->orderBy('d')
      ->get();

    // Normalize to daily series
    $map = [];
    foreach ($rows as $r) {
      $map[$r->d][$r->type] = (int) $r->c;
    }

    $labels = [];
    $opens = [];
    $clicks = [];
    for ($i = $days - 1; $i >= 0; $i--) {
      $d = now()->subDays($i)->toDateString();
      $labels[] = $d;
      $opens[] = (int) ($map[$d]['open'] ?? 0);
      $clicks[] = (int) ($map[$d]['click'] ?? 0);
    }

    return response()->json([
      'ok' => true,
      'data' => [
        'labels' => $labels,
        'opens' => $opens,
        'clicks' => $clicks,
      ],
    ]);
  }

  public function topLinks(Request $request)
  {
    $days = max(7, min(180, (int) $request->query('days', 30)));

    // clicks aggregated by outbound_uuid + hash
    $clicks = EmailEvent::query()
      ->where('type', 'click')
      ->where('occurred_at', '>=', now()->subDays($days)->startOfDay())
      ->get(['outbound_uuid', 'meta_json']);

    $counts = [];
    foreach ($clicks as $ev) {
      $uuid = $ev->outbound_uuid;
      $hash = $ev->meta_json['hash'] ?? null;
      if (!$uuid || !$hash)
        continue;
      $k = $uuid . '::' . $hash;
      $counts[$k] = ($counts[$k] ?? 0) + 1;
    }

    arsort($counts);
    $top = array_slice($counts, 0, 10, true);

    $rows = [];
    foreach ($top as $k => $c) {
      [$uuid, $hash] = explode('::', $k, 2);
      $link = OutboundLink::query()
        ->where('outbound_uuid', $uuid)
        ->where('hash', $hash)
        ->first();
      $rows[] = [
        'outbound_uuid' => $uuid,
        'hash' => $hash,
        'url' => $link?->url,
        'clicks' => (int) $c,
      ];
    }

    return response()->json([
      'ok' => true,
      'data' => [
        'items' => $rows,
      ],
    ]);
  }
}
