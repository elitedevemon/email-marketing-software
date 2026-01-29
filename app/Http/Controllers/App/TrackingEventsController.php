<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TrackingEventsController extends Controller
{
  public function index()
  {
    return view('app.tracking.events');
  }
}
