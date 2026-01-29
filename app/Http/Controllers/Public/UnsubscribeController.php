<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\UnsubscribeEvent;
use App\Services\SuppressionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class UnsubscribeController extends Controller
{
  public function __invoke(Request $request, SuppressionService $suppression)
  {
    $token = (string) $request->query('t', '');
    $clientId = $request->integer('c') ?: null;
    $outboundUuid = $request->query('o') ?: null;

    try {
      $email = Crypt::decryptString($token);
    } catch (\Throwable $e) {
      abort(400);
    }

    $email = $suppression->normalizeEmail($email);
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      abort(400);
    }

    $suppression->suppress(
      email: $email,
      reason: 'Unsubscribed',
      source: 'unsubscribe',
      clientId: $clientId,
      meta: ['outbound_uuid' => $outboundUuid],
    );

    UnsubscribeEvent::query()->create([
      'email' => $email,
      'client_id' => $clientId,
      'outbound_uuid' => $outboundUuid,
      'ip' => $request->ip(),
      'user_agent' => substr((string) $request->userAgent(), 0, 1000),
    ]);

    if ($clientId) {
      Client::query()->whereKey($clientId)->update([
        'status' => 'suppressed',
      ]);
    }

    return response()->view('public.unsubscribed', [
      'masked' => $this->maskEmail($email),
    ]);
  }

  private function maskEmail(string $email): string
  {
    [$u, $d] = array_pad(explode('@', $email, 2), 2, '');
    $uMasked = strlen($u) <= 2 ? str_repeat('*', strlen($u)) : substr($u, 0, 2) . str_repeat('*', max(1, strlen($u) - 2));
    return $uMasked . '@' . $d;
  }
}