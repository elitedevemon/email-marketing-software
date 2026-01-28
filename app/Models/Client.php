<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'uuid',
    'business_name',
    'contact_name',
    'email',
    'website_url',
    'city',
    'country',
    'category_id',
    'status',
  ];

  public function category()
  {
    return $this->belongsTo(Category::class);
  }

  public function tags()
  {
    return $this->belongsToMany(Tag::class, 'client_tag');
  }

  public function notes()
  {
    return $this->hasMany(ClientNote::class);
  }

  public function competitors()
  {
    return $this->hasMany(Competitor::class);
  }
}
