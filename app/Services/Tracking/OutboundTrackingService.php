<?php

namespace App\Services\Tracking;

use App\Models\OutboundLink;
use Illuminate\Support\Str;

class OutboundTrackingService
{
  public function applyToHtml(string $html, string $outboundUuid, string $unsubscribeUrl): string
  {
    $html = $this->rewriteLinks($html, $outboundUuid, $unsubscribeUrl);
    $html = $this->appendFooterAndPixel($html, $outboundUuid, $unsubscribeUrl);
    return $html;
  }

  public function applyToText(string $text, string $unsubscribeUrl): string
  {
    $text = rtrim($text);
    return $text . "\n\nUnsubscribe: " . $unsubscribeUrl . "\n";
  }

  private function rewriteLinks(string $html, string $outboundUuid, string $unsubscribeUrl): string
  {
    $pattern = '/href\s*=\s*(["\'])(.*?)\1/i';

    return preg_replace_callback($pattern, function ($m) use ($outboundUuid, $unsubscribeUrl) {
      $quote = $m[1];
      $url = trim($m[2]);

      if ($url === '' || str_starts_with($url, '#'))
        return $m[0];
      if (Str::startsWith($url, ['mailto:', 'tel:']))
        return $m[0];
      // do not rewrite unsubscribe link or already-tracked links
      if ($url === $unsubscribeUrl)
        return $m[0];
      if (Str::contains($url, '/t/c/'))
        return $m[0];
      // only rewrite absolute http(s)
      if (!Str::startsWith($url, ['http://', 'https://']))
        return $m[0];
      $hash = substr(hash('sha256', $url), 0, 12);
      OutboundLink::query()->updateOrCreate(
        ['outbound_uuid' => $outboundUuid, 'hash' => $hash],
        ['url' => $url],
      );

      $trackUrl = route('public.track.click', ['uuid' => $outboundUuid, 'hash' => $hash]);
      return 'href=' . $quote . $trackUrl . $quote;
    }, $html) ?? $html;
  }

  private function appendFooterAndPixel(string $html, string $outboundUuid, string $unsubscribeUrl): string
  {
    $pixelUrl = route('public.track.open', ['uuid' => $outboundUuid]);
    $footer = <<<HTML
    <hr style="border:none;border-top:1px solid rgba(0,0,0,0.08);margin:16px 0" />
    <p style="margin:0;font-size:12px;line-height:1.5;color:rgba(0,0,0,0.6)">
    If you don’t want to receive these emails, you can
    <a href="{$unsubscribeUrl}" style="color:inherit;text-decoration:underline">unsubscribe</a>.
    </p>
    <img src="{$pixelUrl}" width="1" height="1" alt="" style="display:none !important" />
    HTML;

    if (stripos($html, '</body>') !== false) {
      return preg_replace('/<\/body>/i', $footer . '</body>', $html, 1) ?? ($html . $footer);
    }

    return $html . $footer;
  }
}