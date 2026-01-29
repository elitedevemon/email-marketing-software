<?php

return [
  // Global kill switch (used by tick + job)
  'sending_enabled' => env('EMAIL_SENDING_ENABLED', true),

  // Per recipient-domain throttle (per minute). Adjust to taste.
  // NOTE: These are conservative defaults to reduce burst patterns.
  'domain_limits' => [
    'gmail.com' => ['per_minute' => 20],
    'googlemail.com' => ['per_minute' => 20],
    'yahoo.com' => ['per_minute' => 15],
    'outlook.com' => ['per_minute' => 15],
    'hotmail.com' => ['per_minute' => 15],
    'live.com' => ['per_minute' => 15],
    '*' => ['per_minute' => 30],
  ],
];