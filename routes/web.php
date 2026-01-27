<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\App\DashboardController;

Route::get('/', function () {
  return view('welcome');
});

// Keep /dashboard for compatibility; redirect into the SaaS shell.
Route::get('/dashboard', function () {
  return redirect()->route('app.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:admin|operator'])
  ->prefix('app')
  ->name('app.')
  ->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
  });

Route::middleware('auth')->group(function () {
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
