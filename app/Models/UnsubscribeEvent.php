<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnsubscribeEvent extends Model
{
  protected $fillable = [
    'email',
    'client_id',
    'outbound_uuid',
    'ip',
    'user_agent',
  ];
}
