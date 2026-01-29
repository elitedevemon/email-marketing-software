<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuppressionEntry extends Model
{
  protected $fillable = [
    'email',
    'reason',
    'source',
    'client_id',
    'meta_json',
  ];

  protected $casts = [
    'meta_json' => 'array',
  ];
}
