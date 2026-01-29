<?php

namespace App\Services\Sending;

class DomainClassifier
{
  public function groupForEmail(?string $email): string
  {
    $domain = '';
    if ($email && str_contains($email, '@')) {
      $domain = strtolower(trim(substr(strrchr($email, '@'), 1)));
    }

    return match (true) {
      in_array($domain, ['gmail.com', 'googlemail.com'], true) => 'gmail',
      in_array($domain, ['yahoo.com', 'yahoo.co.uk', 'ymail.com'], true) => 'yahoo',
      in_array($domain, ['outlook.com', 'hotmail.com', 'live.com', 'msn.com'], true) => 'outlook',
      default => 'other',
    };
  }
}