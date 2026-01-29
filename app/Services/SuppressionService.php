<?php

namespace App\Services;

use App\Models\SuppressionEntry;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;

class SuppressionService
{
  public function normalizeEmail(?string $email): ?string
  {
    if ($email === null)
      return null;
    $email = strtolower(trim($email));
    return $email !== '' ? $email : null;
  }

  public function isSuppressed(?string $email): bool
  {
    $email = $this->normalizeEmail($email);
    if (!$email)
      return false;
    return SuppressionEntry::query()->where('email', $email)->exists();
  }

  public function suppress(string $email, ?string $reason = null, string $source = 'manual', ?int $clientId = null, array $meta = []): SuppressionEntry
  {
    $email = $this->normalizeEmail($email) ?? $email;

    return SuppressionEntry::query()->updateOrCreate(
      ['email' => $email],
      [
        'reason' => $reason,
        'source' => $source,
        'client_id' => $clientId,
        'meta_json' => $meta ?: null,
      ],
    );
  }

  public function makeSignedUnsubscribeUrl(string $email, ?int $clientId = null, ?string $outboundUuid = null): string
  {
    $token = Crypt::encryptString($this->normalizeEmail($email) ?? $email);

    // long-lived signed URL (works for already-sent emails)
    return URL::temporarySignedRoute(
      'public.unsubscribe',
      now()->addYears(5),
      [
        't' => $token,
        'c' => $clientId,
        'o' => $outboundUuid,
      ],
    );
  }
}