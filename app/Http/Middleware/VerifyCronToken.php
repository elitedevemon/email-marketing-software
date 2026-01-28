<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCronToken
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next)
  {
    $expected = (string) env('CRON_TOKEN', '');
    if ($expected === '') {
      return response()->json([
        'ok' => false,
        'message' => 'CRON_TOKEN is not configured.',
      ], 503);
    }

    $token = '';
    $auth = (string) $request->header('Authorization', '');
    if (str_starts_with($auth, 'Bearer ')) {
      $token = substr($auth, 7);
    }
    if ($token === '') {
      $token = (string) $request->query('token', '');
    }

    if (!hash_equals($expected, (string) $token)) {
      return response()->json([
        'ok' => false,
        'message' => 'Unauthorized.',
      ], 401);
    }

    return $next($request);
  }
}
