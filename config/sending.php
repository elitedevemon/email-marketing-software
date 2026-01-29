<?php

return [
  // Global fallback (sender-specific window overrides if present)
  'window' => [
    'start' => env('SENDING_WINDOW_START', '09:00'),
    'end' => env('SENDING_WINDOW_END', '21:00'),
    'timezone' => env('SENDING_TIMEZONE', config('app.timezone', 'Asia/Dhaka')),
  ],

  // Random jitter in seconds (sender-specific jitter overrides if present)
  'jitter' => [
    'min' => (int) env('SENDING_JITTER_MIN', 15),
    'max' => (int) env('SENDING_JITTER_MAX', 180),
  ],

  // Per-domain min interval (seconds) between sends (bucketed)
  'domain_intervals' => [
    'gmail' => (int) env('DOMAIN_INTERVAL_GMAIL', 2),
    'yahoo' => (int) env('DOMAIN_INTERVAL_YAHOO', 3),
    'outlook' => (int) env('DOMAIN_INTERVAL_OUTLOOK', 2),
    'other' => (int) env('DOMAIN_INTERVAL_OTHER', 1),
  ],

  // Backoff base seconds when domain/provider errors happen
  'domain_backoff_base' => (int) env('DOMAIN_BACKOFF_BASE', 30),
  'domain_backoff_max' => (int) env('DOMAIN_BACKOFF_MAX', 900), // 15 min cap
];