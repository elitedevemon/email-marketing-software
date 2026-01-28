<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class ClientController extends Controller
{
  public function index()
  {
    $categories = Category::query()
      ->orderBy('sort_order')
      ->orderBy('name')
      ->get(['id', 'name']);

    return view('app.clients.index', compact('categories'));
  }
}
