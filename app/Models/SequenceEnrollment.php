<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SequenceEnrollment extends Model
{
  protected $fillable = [
    'client_id',
    'sequence_id',
    'status',
    'current_step_order',
    'next_run_at',
    'started_at',
    'stopped_at',
    'stop_reason',
  ];

  protected $casts = [
    'current_step_order' => 'integer',
    'next_run_at' => 'datetime',
    'started_at' => 'datetime',
    'stopped_at' => 'datetime',
  ];
}
