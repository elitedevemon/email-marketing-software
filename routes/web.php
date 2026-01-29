<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\CategoryController;
use App\Http\Controllers\Ajax\CategoryAjaxController;
use App\Http\Controllers\App\ClientController;
use App\Http\Controllers\Ajax\ClientAjaxController;
use App\Http\Controllers\Ajax\CompetitorAjaxController;
use App\Http\Controllers\App\SenderController;
use App\Http\Controllers\App\FailedJobsController;
use App\Http\Controllers\Ajax\SenderAjaxController;
use App\Http\Controllers\Ajax\FailedJobsAjaxController;
use App\Http\Controllers\Cron\CronRunController;
use App\Http\Controllers\Public\UnsubscribeController;
use App\Http\Controllers\Public\TrackingController;
use App\Http\Controllers\App\SuppressionController;
use App\Http\Controllers\Ajax\SuppressionAjaxController;
use App\Http\Controllers\App\SendingLogController;
use App\Http\Controllers\Ajax\SendingLogAjaxController;
use App\Http\Controllers\App\TrackingEventsController;
use App\Http\Controllers\Ajax\TrackingEventsAjaxController;
use App\Http\Controllers\Ajax\TrackingWidgetsAjaxController;

Route::get('/', function () {
  return view('welcome');
});

// ----------------------------
// Public: Unsubscribe + Tracking
// ----------------------------
Route::get('/u', UnsubscribeController::class)
  ->name('public.unsubscribe')
  ->middleware(['signed', 'throttle:60,1']);

Route::get('/t/o/{uuid}.gif', [TrackingController::class, 'open'])
  ->name('public.track.open')
  ->middleware(['throttle:240,1']);

Route::get('/t/c/{uuid}/{hash}', [TrackingController::class, 'click'])
  ->name('public.track.click')
  ->middleware(['throttle:240,1']);


// Secure cron trigger (external cron websites)
Route::get('/cron/run', [CronRunController::class, 'run'])
  ->middleware(['cron.token', 'throttle:10,10'])
  ->name('cron.run');

// Keep /dashboard for compatibility; redirect into the SaaS shell.
Route::get('/dashboard', function () {
  return redirect()->route('app.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:admin|operator'])
  ->prefix('app')
  ->name('app.')
  ->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/sending/logs', [SendingLogController::class, 'index'])
      ->name('sending.logs');

    Route::get('/tracking/events', [TrackingEventsController::class, 'index'])
      ->name('tracking.events');

    // Categories (page)
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

    // Clients (page)
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');

    // Senders (page)
    Route::get('/senders', [SenderController::class, 'index'])->name('senders.index');

    // Queue / Failed jobs (page)
    Route::get('/queue/failed', [FailedJobsController::class, 'index'])->name('queue.failed');

    Route::get('/suppression', [SuppressionController::class, 'index'])
      ->name('app.suppression.index');

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

      // Competitors (per client)
      Route::get('/clients/{client}/competitors', [CompetitorAjaxController::class, 'index'])->name('clients.competitors.index');
      Route::post('/clients/{client}/competitors', [CompetitorAjaxController::class, 'store'])->name('clients.competitors.store');
      Route::patch('/competitors/{competitor}', [CompetitorAjaxController::class, 'update'])->name('competitors.update');
      Route::delete('/competitors/{competitor}', [CompetitorAjaxController::class, 'destroy'])->name('competitors.destroy');

      // Senders
      Route::get('/senders', [SenderAjaxController::class, 'index'])->name('senders.index');
      Route::post('/senders', [SenderAjaxController::class, 'store'])->name('senders.store');
      Route::patch('/senders/{sender}', [SenderAjaxController::class, 'update'])->name('senders.update');
      Route::delete('/senders/{sender}', [SenderAjaxController::class, 'destroy'])->name('senders.destroy');

      // Failed jobs
      Route::get('/failed-jobs', [FailedJobsAjaxController::class, 'index'])->name('failed-jobs.index');
      Route::get('/failed-jobs/{id}', [FailedJobsAjaxController::class, 'show'])->name('failed-jobs.show');
      Route::post('/failed-jobs/{id}/retry', [FailedJobsAjaxController::class, 'retry'])
        ->middleware('role:admin')
        ->name('failed-jobs.retry');
      Route::delete('/failed-jobs/{id}', [FailedJobsAjaxController::class, 'forget'])
        ->middleware('role:admin')
        ->name('failed-jobs.forget');

      Route::get('/suppression', [SuppressionAjaxController::class, 'index'])
        ->name('ajax.suppression.index');
      Route::post('/suppression', [SuppressionAjaxController::class, 'store'])
        ->name('ajax.suppression.store');
      Route::delete('/suppression/{suppressionEntry}', [SuppressionAjaxController::class, 'destroy'])
        ->name('ajax.suppression.destroy');

      Route::get('/sending/logs', [SendingLogAjaxController::class, 'index'])
        ->name('ajax.sending.logs');

      Route::get('/tracking/events', [TrackingEventsAjaxController::class, 'index'])
        ->name('ajax.tracking.events');
      Route::get('/tracking/outbound/{uuid}', [TrackingEventsAjaxController::class, 'outbound'])
        ->name('ajax.tracking.outbound');

      // Dashboard widgets
      Route::get('/widgets/tracking/trend', [TrackingWidgetsAjaxController::class, 'trend'])
        ->name('ajax.widgets.tracking.trend');
      Route::get('/widgets/tracking/top-links', [TrackingWidgetsAjaxController::class, 'topLinks'])
        ->name('ajax.widgets.tracking.topLinks');
    });
  });

Route::middleware('auth')->group(function () {
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
