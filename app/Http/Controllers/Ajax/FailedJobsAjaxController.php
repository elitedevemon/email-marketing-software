<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class FailedJobsAjaxController extends Controller
{
  public function index(Request $request)
  {
    $q = trim((string) $request->query('q', ''));
    $queue = trim((string) $request->query('queue', ''));

    $query = DB::table('failed_jobs')->orderByDesc('failed_at');

    if ($q !== '') {
      $like = '%' . addcslashes($q, '%_') . '%';
      $query->where(function ($w) use ($like) {
        $w->where('connection', 'like', $like)
          ->orWhere('queue', 'like', $like)
          ->orWhere('exception', 'like', $like);
      });
    }

    if ($queue !== '') {
      $query->where('queue', $queue);
    }

    $p = $query->paginate(20)->withQueryString();

    return response()->json([
      'ok' => true,
      'data' => [
        'rows' => collect($p->items())->map(function ($r) {
          $exc = (string) ($r->exception ?? '');
          $snippet = mb_substr($exc, 0, 280);
          return [
            'id' => (int) $r->id,
            'uuid' => (string) $r->uuid,
            'connection' => (string) $r->connection,
            'queue' => (string) $r->queue,
            'failed_at' => (string) $r->failed_at,
            'exception_snippet' => $snippet,
          ];
        })->values(),
        'pagination' => [
          'page' => $p->currentPage(),
          'per_page' => $p->perPage(),
          'total' => $p->total(),
          'last_page' => $p->lastPage(),
        ],
      ],
    ]);
  }

  public function show(int $id)
  {
    $r = DB::table('failed_jobs')->where('id', $id)->first();
    if (!$r) {
      return response()->json(['ok' => false, 'message' => 'Failed job not found'], 404);
    }

    return response()->json([
      'ok' => true,
      'data' => [
        'id' => (int) $r->id,
        'uuid' => (string) $r->uuid,
        'connection' => (string) $r->connection,
        'queue' => (string) $r->queue,
        'failed_at' => (string) $r->failed_at,
        'payload' => (string) $r->payload,
        'exception' => (string) $r->exception,
      ],
    ]);
  }

  public function retry(int $id)
  {
    $r = DB::table('failed_jobs')->where('id', $id)->first();
    if (!$r) {
      return response()->json(['ok' => false, 'message' => 'Failed job not found'], 404);
    }

    try {
      // Push raw payload back to queue
      app('queue')->connection($r->connection)->pushRaw($r->payload, $r->queue);
      // Remove from failed list
      app('queue.failer')->forget($id);
    } catch (Throwable $e) {
      return response()->json([
        'ok' => false,
        'message' => 'Retry failed: ' . $e->getMessage(),
      ], 500);
    }

    return response()->json([
      'ok' => true,
      'message' => 'Job retried (re-queued)',
    ]);
  }

  public function forget(int $id)
  {
    $r = DB::table('failed_jobs')->where('id', $id)->first();
    if (!$r) {
      return response()->json(['ok' => false, 'message' => 'Failed job not found'], 404);
    }

    app('queue.failer')->forget($id);

    return response()->json([
      'ok' => true,
      'message' => 'Failed job removed',
    ]);
  }
}
