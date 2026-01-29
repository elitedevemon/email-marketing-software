<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SuppressionController extends Controller
{
  public function index()
  {
    return view('app.suppression.index');
  }
}
