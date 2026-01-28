<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailOutbound extends Model
{
  protected $fillable = [
    'client_id',
    'sender_id',
    'sequence_enrollment_id',
    'sequence_step_id',
    'subject',
    'body_html',
    'body_text',
    'status',
    'scheduled_at',
    'queued_at',
    'sent_at',
    'attempts',
    'last_error',
  ];

  protected $casts = [
    'scheduled_at' => 'datetime',
    'queued_at' => 'datetime',
    'sent_at' => 'datetime',
    'attempts' => 'integer',
  ];
}
