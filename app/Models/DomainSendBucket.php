<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomainSendBucket extends Model
{
  protected $fillable = [
    'domain',
    'bucket_at',
    'sent_count',
  ];

  protected $casts = [
    'bucket_at' => 'datetime',
  ];
}
