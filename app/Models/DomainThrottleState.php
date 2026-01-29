<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomainThrottleState extends Model
{
  protected $fillable = ['group', 'next_available_at', 'error_streak', 'last_error_at'];
  protected $casts = [
    'next_available_at' => 'datetime',
    'last_error_at' => 'datetime',
  ];
}
