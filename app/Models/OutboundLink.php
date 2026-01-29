<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutboundLink extends Model
{
  protected $fillable = [
    'outbound_uuid',
    'hash',
    'url',
  ];
}
