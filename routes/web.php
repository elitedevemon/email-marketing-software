<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\CategoryController;
use App\Http\Controllers\Ajax\CategoryAjaxController;
use App\Http\Controllers\App\ClientController;
use App\Http\Controllers\Ajax\ClientAjaxController;

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

  // Categories (page)
  Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

  // Clients (page)
  Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');

  // AJAX (JSON)
  Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('/categories', [CategoryAjaxController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryAjaxController::class, 'store'])->name('categories.store');
    Route::patch('/categories/{category}', [CategoryAjaxController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryAjaxController::class, 'destroy'])->name('categories.destroy');

    // Clients
    Route::get('/clients', [ClientAjaxController::class, 'index'])->name('clients.index');
    Route::post('/clients', [ClientAjaxController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}', [ClientAjaxController::class, 'show'])->name('clients.show');
    Route::patch('/clients/{client}', [ClientAjaxController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [ClientAjaxController::class, 'destroy'])->name('clients.destroy');

    // Client notes
    Route::get('/clients/{client}/notes', [ClientAjaxController::class, 'notesIndex'])->name('clients.notes.index');
    Route::post('/clients/{client}/notes', [ClientAjaxController::class, 'notesStore'])->name('clients.notes.store');
  });
  });

Route::middleware('auth')->group(function () {
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
