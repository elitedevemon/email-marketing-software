<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sender extends Model
{
  protected $fillable = [
    'name',
    'from_name',
    'from_email',
    'is_active',

    'daily_limit',
    'sent_today',
    'sent_today_date',
    'window_start',
    'window_end',
    'timezone',
    'jitter_min_seconds',
    'jitter_max_seconds',

    'smtp_host',
    'smtp_port',
    'smtp_encryption',
    'smtp_username',
    'smtp_password',

    'imap_host',
    'imap_port',
    'imap_encryption',
    'imap_username',
    'imap_password',

    'last_sent_at',
  ];

  protected $casts = [
    'is_active' => 'boolean',
    'daily_limit' => 'integer',
    'sent_today' => 'integer',
    'sent_today_date' => 'date',
    'smtp_password' => 'encrypted',
    'imap_password' => 'encrypted',
    'last_sent_at' => 'datetime',
  ];
}
