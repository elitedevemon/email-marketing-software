<?php

namespace App\Http\Controllers\Cron;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CronRun;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Throwable;

class CronRunController extends Controller
{
  public function run(Request $request)
  {
    $started = microtime(true);

    // Global lock to prevent double schedule runs (plus DB idempotency downstream)
    $lock = Cache::lock('cron:schedule-run', 600);
    if (!$lock->get()) {
      CronRun::create([
        'status' => 'skipped',
        'duration_ms' => 0,
        'ip' => $request->ip(),
        'user_agent' => substr((string) $request->userAgent(), 0, 255),
        'output' => 'Lock busy: cron already running.',
      ]);

      return response()->json([
        'ok' => false,
        'message' => 'Cron is already running.',
      ], 409);
    }

    try {
      Artisan::call('schedule:run', [
        '--verbose' => true,
        '--no-interaction' => true,
      ]);
      $output = Artisan::output();

      $durationMs = (int) round((microtime(true) - $started) * 1000);
      $run = CronRun::create([
        'status' => 'ok',
        'duration_ms' => $durationMs,
        'ip' => $request->ip(),
        'user_agent' => substr((string) $request->userAgent(), 0, 255),
        'output' => mb_substr((string) $output, 0, 65000),
      ]);

      return response()->json([
        'ok' => true,
        'message' => 'Schedule executed.',
        'data' => [
          'cron_run_id' => $run->id,
          'duration_ms' => $durationMs,
        ],
      ]);
    } catch (Throwable $e) {
      $durationMs = (int) round((microtime(true) - $started) * 1000);
      CronRun::create([
        'status' => 'fail',
        'duration_ms' => $durationMs,
        'ip' => $request->ip(),
        'user_agent' => substr((string) $request->userAgent(), 0, 255),
        'output' => mb_substr($e->getMessage() . "\n" . $e->getTraceAsString(), 0, 65000),
      ]);

      return response()->json([
        'ok' => false,
        'message' => 'Schedule failed.',
      ], 500);
    } finally {
      optional($lock)->release();
    }
  }
}
