<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\EmailEvent;
use App\Models\OutboundLink;
use App\Models\EmailOutbound; // assumes Step 6 created this model
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TrackingController extends Controller
{
  public function open(Request $request, string $uuid)
  {
    $this->recordEvent($request, $uuid, 'open', null);

    // 1x1 transparent GIF
    $gif = base64_decode('R0lGODlhAQABAPAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
    return response($gif, 200, [
      'Content-Type' => 'image/gif',
      'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
      'Pragma' => 'no-cache',
    ]);
  }

  public function click(Request $request, string $uuid, string $hash)
  {
    $link = OutboundLink::query()
      ->where('outbound_uuid', $uuid)
      ->where('hash', $hash)
      ->first();

    $this->recordEvent($request, $uuid, 'click', [
      'hash' => $hash,
      'url' => $link?->url,
    ]);

    return redirect()->away($link?->url ?: config('app.url'), 302);
  }

  private function recordEvent(Request $request, string $uuid, string $type, ?array $meta): void
  {
    if (!Str::isUuid($uuid)) {
      return; // always respond same, but skip DB write
    }

    $ip = $request->ip();
    $ua = substr((string) $request->userAgent(), 0, 1000);
    $today = Carbon::now()->toDateString();

    // soft-dedupe: same uuid+type+ip+day
    $exists = EmailEvent::query()
      ->where('outbound_uuid', $uuid)
      ->where('type', $type)
      ->where('ip', $ip)
      ->whereDate('occurred_at', $today)
      ->exists();

    if ($exists)
      return;

    $outbound = class_exists(EmailOutbound::class)
      ? EmailOutbound::query()->where('uuid', $uuid)->first()
      : null;

    EmailEvent::query()->create([
      'outbound_uuid' => $uuid,
      'client_id' => $outbound?->client_id,
      'sender_id' => $outbound?->sender_id,
      'type' => $type,
      'occurred_at' => now(),
      'ip' => $ip,
      'user_agent' => $ua,
      'meta_json' => $meta ?: null,
    ]);
  }
}