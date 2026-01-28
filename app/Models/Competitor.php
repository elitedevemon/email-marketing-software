<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Competitor extends Model
{
  protected $fillable = [
    'client_id',
    'name',
    'website_url',
    'insights_json',
  ];

  protected $casts = [
    'insights_json' => 'array',
  ];

  public function client()
  {
    return $this->belongsTo(Client::class);
  }
}
