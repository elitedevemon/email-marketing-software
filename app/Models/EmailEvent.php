<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailEvent extends Model
{
  protected $fillable = [
    'outbound_uuid',
    'client_id',
    'sender_id',
    'type',
    'occurred_at',
    'ip',
    'user_agent',
    'meta_json',
  ];

  protected $casts = [
    'occurred_at' => 'datetime',
    'meta_json' => 'array',
  ];
}
