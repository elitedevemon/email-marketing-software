<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SenderController extends Controller
{
  public function index()
  {
    return view('app.senders.index');
  }
}
