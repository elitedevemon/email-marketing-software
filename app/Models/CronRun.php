<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CronRun extends Model
{
  protected $fillable = [
    'status',
    'duration_ms',
    'ip',
    'user_agent',
    'output',
  ];
}
