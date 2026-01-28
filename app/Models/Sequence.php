<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sequence extends Model
{
  protected $fillable = [
    'key',
    'name',
    'description',
    'is_active',
  ];

  protected $casts = [
    'is_active' => 'boolean',
  ];

  public function steps()
  {
    return $this->hasMany(SequenceStep::class)->orderBy('step_order');
  }
}
