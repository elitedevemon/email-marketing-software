<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\EmailEvent;
use App\Models\OutboundLink;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
class TrackingEventsAjaxController extends Controller
{
  public function index(Request $request)
  {
    $search = trim((string) $request->query('search', ''));
    $type = trim((string) $request->query('type', '')); // open|click|empty
    $from = trim((string) $request->query('from', '')); // YYYY-MM-DD
    $to = trim((string) $request->query('to', ''));     // YYYY-MM-DD

    $q = EmailEvent::query()->orderByDesc('id');

    if ($type !== '') {
      $q->where('type', $type);
    }

    if ($from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
      $q->whereDate('occurred_at', '>=', $from);
    }
    if ($to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
      $q->whereDate('occurred_at', '<=', $to);
    }

    if ($search !== '') {
      $q->where(function ($qq) use ($search) {
        $qq->where('outbound_uuid', 'like', "%{$search}%")
          ->orWhere('ip', 'like', "%{$search}%");
      });
    }

    $rows = $q->paginate(20);

    return response()->json([
      'ok' => true,
      'data' => [
        'items' => $rows->items(),
        'meta' => [
          'current_page' => $rows->currentPage(),
          'last_page' => $rows->lastPage(),
          'per_page' => $rows->perPage(),
          'total' => $rows->total(),
        ],
      ],
    ]);
  }

  public function outbound(Request $request, string $uuid)
  {
    if (!Str::isUuid($uuid)) {
      return response()->json(['ok' => false, 'message' => 'Invalid uuid.'], 422);
    }

    $from = $request->query('from');
    $to = $request->query('to');

    $eventsQ = EmailEvent::query()->where('outbound_uuid', $uuid);
    if ($from && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from))
      $eventsQ->whereDate('occurred_at', '>=', $from);
    if ($to && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))
      $eventsQ->whereDate('occurred_at', '<=', $to);

    $opens = (clone $eventsQ)->where('type', 'open')->count();
    $clicks = (clone $eventsQ)->where('type', 'click')->count();

    // Clicked links aggregation (uses outbound_links + click meta hash)
    $clickEvents = (clone $eventsQ)->where('type', 'click')->get(['meta_json']);
    $hashCounts = [];
    foreach ($clickEvents as $ev) {
      $hash = $ev->meta_json['hash'] ?? null;
      if (!$hash)
        continue;
      $hashCounts[$hash] = ($hashCounts[$hash] ?? 0) + 1;
    }

    $links = OutboundLink::query()->where('outbound_uuid', $uuid)->get(['hash', 'url']);
    $linkRows = $links->map(function ($l) use ($hashCounts) {
      return [
        'hash' => $l->hash,
        'url' => $l->url,
        'clicks' => (int) ($hashCounts[$l->hash] ?? 0),
      ];
    })->sortByDesc('clicks')->values()->all();

    return response()->json([
      'ok' => true,
      'data' => [
        'uuid' => $uuid,
        'opens' => $opens,
        'clicks' => $clicks,
        'links' => $linkRows,
      ],
    ]);
  }
}
