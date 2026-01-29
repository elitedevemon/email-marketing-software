<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\SuppressionEntry;
use App\Services\SuppressionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SuppressionAjaxController extends Controller
{
  public function index(Request $request)
  {
    $search = trim((string) $request->query('search', ''));

    $q = SuppressionEntry::query()->orderByDesc('id');
    if ($search !== '') {
      $q->where(function ($qq) use ($search) {
        $qq->where('email', 'like', "%{$search}%")
          ->orWhere('reason', 'like', "%{$search}%")
          ->orWhere('source', 'like', "%{$search}%");
      });
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

  public function store(Request $request, SuppressionService $suppression)
  {
    $validated = $request->validate([
      'email' => ['required', 'email:rfc,dns', 'max:191'],
      'reason' => ['nullable', 'string', 'max:191'],
      'source' => ['nullable', 'string', 'max:50'],
      'client_id' => ['nullable', 'integer'],
    ]);

    $email = $suppression->normalizeEmail($validated['email']);

    $entry = SuppressionEntry::query()->updateOrCreate(
      ['email' => $email],
      [
        'reason' => $validated['reason'] ?? null,
        'source' => $validated['source'] ?? 'manual',
        'client_id' => $validated['client_id'] ?? null,
      ],
    );

    return response()->json([
      'ok' => true,
      'message' => 'Suppressed.',
      'data' => ['item' => $entry],
    ]);
  }

  public function destroy(SuppressionEntry $suppressionEntry)
  {
    $suppressionEntry->delete();

    return response()->json([
      'ok' => true,
      'message' => 'Removed from suppression.',
    ]);
  }
}