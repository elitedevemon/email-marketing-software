<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\Sender;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SenderAjaxController extends Controller
{
  public function index(Request $request)
  {
    $q = trim((string) $request->query('q', ''));
    $status = (string) $request->query('status', 'all'); // all|active|inactive

    $query = Sender::query();

    if ($q !== '') {
      $like = '%' . addcslashes($q, '%_') . '%';
      $query->where(function ($w) use ($like) {
        $w->where('name', 'like', $like)
          ->orWhere('from_email', 'like', $like)
          ->orWhere('smtp_username', 'like', $like);
      });
    }

    if ($status === 'active')
      $query->where('is_active', true);
    if ($status === 'inactive')
      $query->where('is_active', false);

    $p = $query->orderByDesc('id')->paginate(20)->withQueryString();

    return response()->json([
      'ok' => true,
      'data' => [
        'rows' => $p->getCollection()->map(function (Sender $s) {
          return [
            'id' => $s->id,
            'name' => $s->name,
            'from_name' => $s->from_name,
            'from_email' => $s->from_email,
            'is_active' => (bool) $s->is_active,
            'daily_limit' => (int) $s->daily_limit,
            'sent_today' => (int) $s->sent_today,
            'window_start' => $s->window_start,
            'window_end' => $s->window_end,
            'timezone' => $s->timezone,
            'jitter_min_seconds' => (int) $s->jitter_min_seconds,
            'jitter_max_seconds' => (int) $s->jitter_max_seconds,

            'smtp_host' => $s->smtp_host,
            'smtp_port' => (int) $s->smtp_port,
            'smtp_encryption' => $s->smtp_encryption,
            'smtp_username' => $s->smtp_username,
            'has_smtp_password' => $s->smtp_password ? true : false,

            'imap_host' => $s->imap_host,
            'imap_port' => $s->imap_port ? (int) $s->imap_port : null,
            'imap_encryption' => $s->imap_encryption,
            'imap_username' => $s->imap_username,
            'has_imap_password' => $s->imap_password ? true : false,

            'last_sent_at' => optional($s->last_sent_at)->toISOString(),
            'created_at' => optional($s->created_at)->toISOString(),
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

  public function store(Request $request)
  {
    $validated = $this->validatePayload($request, isCreate: true);

    // IMAP sanity (optional)
    if (!empty($validated['imap_host']) && empty($validated['imap_password'])) {
      return response()->json([
        'ok' => false,
        'message' => 'IMAP password is required when IMAP host is set.',
        'errors' => ['imap_password' => ['IMAP password is required when IMAP host is set.']],
      ], 422);
    }

    $sender = Sender::create([
      'name' => $validated['name'],
      'from_name' => $validated['from_name'],
      'from_email' => strtolower($validated['from_email']),
      'is_active' => (bool) ($validated['is_active'] ?? true),

      'daily_limit' => (int) $validated['daily_limit'],
      'sent_today' => 0,
      'sent_today_date' => now()->toDateString(),
      'window_start' => $validated['window_start'],
      'window_end' => $validated['window_end'],
      'timezone' => $validated['timezone'],
      'jitter_min_seconds' => (int) $validated['jitter_min_seconds'],
      'jitter_max_seconds' => (int) $validated['jitter_max_seconds'],

      'smtp_host' => $validated['smtp_host'],
      'smtp_port' => (int) $validated['smtp_port'],
      'smtp_encryption' => $validated['smtp_encryption'],
      'smtp_username' => $validated['smtp_username'],
      'smtp_password' => $validated['smtp_password'], // encrypted cast

      'imap_host' => $validated['imap_host'] ?? null,
      'imap_port' => $validated['imap_port'] ?? null,
      'imap_encryption' => $validated['imap_encryption'] ?? null,
      'imap_username' => $validated['imap_username'] ?? null,
      'imap_password' => $validated['imap_password'] ?? null, // encrypted cast
    ]);

    return response()->json([
      'ok' => true,
      'message' => 'Sender created',
      'data' => ['id' => $sender->id],
    ], 201);
  }

  public function update(Request $request, Sender $sender)
  {
    $validated = $this->validatePayload($request, isCreate: false, senderId: $sender->id);

    // IMAP sanity: if host is set and there is no existing password and no new password -> error
    $imapHost = $validated['imap_host'] ?? null;
    $newImapPass = $validated['imap_password'] ?? null;
    if (!empty($imapHost) && empty($sender->imap_password) && empty($newImapPass)) {
      return response()->json([
        'ok' => false,
        'message' => 'IMAP password is required when IMAP host is set.',
        'errors' => ['imap_password' => ['IMAP password is required when IMAP host is set.']],
      ], 422);
    }

    $sender->fill([
      'name' => $validated['name'],
      'from_name' => $validated['from_name'],
      'from_email' => strtolower($validated['from_email']),
      'is_active' => (bool) ($validated['is_active'] ?? true),

      'daily_limit' => (int) $validated['daily_limit'],
      'window_start' => $validated['window_start'],
      'window_end' => $validated['window_end'],
      'timezone' => $validated['timezone'],
      'jitter_min_seconds' => (int) $validated['jitter_min_seconds'],
      'jitter_max_seconds' => (int) $validated['jitter_max_seconds'],

      'smtp_host' => $validated['smtp_host'],
      'smtp_port' => (int) $validated['smtp_port'],
      'smtp_encryption' => $validated['smtp_encryption'],
      'smtp_username' => $validated['smtp_username'],

      'imap_host' => $validated['imap_host'] ?? null,
      'imap_port' => $validated['imap_port'] ?? null,
      'imap_encryption' => $validated['imap_encryption'] ?? null,
      'imap_username' => $validated['imap_username'] ?? null,
    ]);

    // Password updates are optional on edit; blank means "keep existing"
    if (!empty($validated['smtp_password'])) {
      $sender->smtp_password = $validated['smtp_password'];
    }
    if (!empty($validated['imap_password'])) {
      $sender->imap_password = $validated['imap_password'];
    }

    $sender->save();

    return response()->json([
      'ok' => true,
      'message' => 'Sender updated',
    ]);
  }

  public function destroy(Sender $sender)
  {
    $sender->delete();

    return response()->json([
      'ok' => true,
      'message' => 'Sender deleted',
    ]);
  }

  private function validatePayload(Request $request, bool $isCreate, ?int $senderId = null): array
  {
    $encryptionRule = Rule::in(['none', 'ssl', 'tls']);

    return $request->validate([
      'name' => ['required', 'string', 'max:120'],
      'from_name' => ['required', 'string', 'max:120'],
      'from_email' => [
        'required',
        'email:rfc,dns',
        'max:190',
        Rule::unique('senders', 'from_email')->ignore($senderId),
      ],
      'is_active' => ['nullable', 'boolean'],

      'daily_limit' => ['required', 'integer', 'min:1', 'max:5000'],
      'window_start' => ['required', 'date_format:H:i'],
      'window_end' => ['required', 'date_format:H:i'],
      'timezone' => ['required', 'timezone'],
      'jitter_min_seconds' => ['required', 'integer', 'min:0', 'max:3600'],
      'jitter_max_seconds' => ['required', 'integer', 'min:0', 'max:3600'],

      'smtp_host' => ['required', 'string', 'max:190'],
      'smtp_port' => ['required', 'integer', 'min:1', 'max:65535'],
      'smtp_encryption' => ['required', $encryptionRule],
      'smtp_username' => ['required', 'string', 'max:190'],
      'smtp_password' => [$isCreate ? 'required' : 'nullable', 'string', 'max:255'],

      // IMAP is optional for now (used later for reply/bounce). If host provided, other fields required.
      'imap_host' => ['nullable', 'string', 'max:190'],
      'imap_port' => ['nullable', 'integer', 'min:1', 'max:65535', 'required_with:imap_host'],
      'imap_encryption' => ['nullable', $encryptionRule, 'required_with:imap_host'],
      'imap_username' => ['nullable', 'string', 'max:190', 'required_with:imap_host'],
      'imap_password' => ['nullable', 'string', 'max:255'], // additional check in store/update
    ]);
  }
}
