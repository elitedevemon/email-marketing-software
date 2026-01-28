<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SequenceStep extends Model
{
  protected $fillable = [
    'sequence_id',
    'step_order',
    'delay_days',
    'subject',
    'body_html',
    'body_text',
    'is_active',
  ];

  protected $casts = [
    'is_active' => 'boolean',
    'delay_days' => 'integer',
    'step_order' => 'integer',
  ];
}
