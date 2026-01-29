<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\EmailSendLog;
use Illuminate\Http\Request;

class SendingLogAjaxController extends Controller
{
  public function index(Request $request)
  {
    $search = trim((string) $request->query('search', ''));
    $status = trim((string) $request->query('status', ''));
    $senderId = $request->integer('sender_id') ?: null;

    $q = EmailSendLog::query()->orderByDesc('id');

    if ($search !== '') {
      $q->where(function ($qq) use ($search) {
        $qq->where('to_email', 'like', "%{$search}%")
          ->orWhere('subject', 'like', "%{$search}%")
          ->orWhere('error_message', 'like', "%{$search}%")
          ->orWhere('outbound_uuid', 'like', "%{$search}%");
      });
    }

    if ($status !== '') {
      $q->where('status', $status);
    }
    if ($senderId) {
      $q->where('sender_id', $senderId);
    }

    $rows = $q->paginate(20);

    return response()->json([
      'ok' => true,
      'data' => [
        'items' => $rows->items(),
        'meta' => [
          'current_page' => $rows->currentPage(),
          'last_page' => $rows->lastPage(),
          'per_page' => $rows->perPage(),
          'total' => $rows->total(),
        ],
      ],
    ]);
  }
}
