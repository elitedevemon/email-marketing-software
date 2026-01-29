<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SenderDailyCounter extends Model
{
  protected $fillable = ['sender_id', 'date', 'sent_count', 'last_sent_at'];
  protected $casts = [
    'date' => 'date',
    'last_sent_at' => 'datetime',
  ];
}
