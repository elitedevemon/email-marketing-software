<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSendLog extends Model
{
  protected $fillable = [
    'email_outbound_id',
    'outbound_uuid',
    'client_id',
    'sender_id',
    'to_email',
    'subject',
    'status',
    'attempt',
    'duration_ms',
    'error_class',
    'error_message',
    'meta_json',
  ];

  protected $casts = [
    'meta_json' => 'array',
  ];
}
