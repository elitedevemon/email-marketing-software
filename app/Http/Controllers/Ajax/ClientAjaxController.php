<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientNote;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Services\SequenceEngine;

class ClientAjaxController extends Controller
{
  public function index(Request $request)
  {
    $q = trim((string) $request->query('q', ''));
    $status = (string) $request->query('status', 'all'); // all|prospect|engaged|suppressed|paused|archived
    $categoryId = $request->query('category_id');
    $tag = trim((string) $request->query('tag', ''));

    $query = Client::query()
      ->with(['category:id,name', 'tags:id,name'])
      ->withCount(['notes', 'competitors']);

    if ($q !== '') {
      $like = '%' . addcslashes($q, '%_') . '%';
      $query->where(function ($w) use ($like) {
        $w->where('business_name', 'like', $like)
          ->orWhere('email', 'like', $like)
          ->orWhere('city', 'like', $like);
      });
    }

    if ($status !== 'all') {
      $allowed = ['prospect', 'engaged', 'suppressed', 'paused', 'archived'];
      if (in_array($status, $allowed, true)) {
        $query->where('status', $status);
      }
    }

    if (!empty($categoryId)) {
      $query->where('category_id', (int) $categoryId);
    }

    if ($tag !== '') {
      $likeTag = '%' . addcslashes($tag, '%_') . '%';
      $query->whereHas('tags', function ($t) use ($likeTag) {
        $t->where('name', 'like', $likeTag);
      });
    }

    $p = $query
      ->orderByDesc('id')
      ->paginate(20)
      ->withQueryString();

    return response()->json([
      'ok' => true,
      'data' => [
        'rows' => $p->getCollection()->map(function (Client $c) {
          return [
            'id' => $c->id,
            'uuid' => $c->uuid,
            'business_name' => $c->business_name,
            'contact_name' => $c->contact_name,
            'email' => $c->email,
            'website_url' => $c->website_url,
            'city' => $c->city,
            'country' => $c->country,
            'status' => $c->status,
            'category' => $c->category ? ['id' => $c->category->id, 'name' => $c->category->name] : null,
            'tags' => $c->tags->map(fn($t) => $t->name)->values(),
            'notes_count' => (int) $c->notes_count,
            'competitors_count' => (int) $c->competitors_count,
            'created_at' => optional($c->created_at)->toISOString(),
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
    $validated = $request->validate([
      'business_name' => ['required', 'string', 'max:160'],
      'contact_name' => ['nullable', 'string', 'max:160'],
      'email' => ['required', 'email:rfc,dns', 'max:190', 'unique:clients,email'],
      'website_url' => ['nullable', 'url', 'max:255'],
      'city' => ['nullable', 'string', 'max:120'],
      'country' => ['nullable', 'string', 'max:120'],
      'category_id' => ['nullable', 'integer', 'exists:categories,id'],
      'status' => ['required', Rule::in(['prospect', 'engaged', 'suppressed', 'paused', 'archived'])],
      'tags' => ['nullable', 'string', 'max:500'], // comma-separated
    ]);

    $client = Client::create([
      'uuid' => (string) Str::uuid(),
      'business_name' => $validated['business_name'],
      'contact_name' => $validated['contact_name'] ?? null,
      'email' => strtolower($validated['email']),
      'website_url' => $validated['website_url'] ?? null,
      'city' => $validated['city'] ?? null,
      'country' => $validated['country'] ?? null,
      'category_id' => $validated['category_id'] ?? null,
      'status' => $validated['status'] ?? 'prospect',
    ]);

    $this->syncTags($client, $validated['tags'] ?? '');

    // Auto-enroll into default sequence (best-effort; does not block client creation)
    try {
      app(SequenceEngine::class)->enrollDefaultForClient($client);
    } catch (\Throwable $e) {
      logger()->warning('Default sequence enrollment failed: ' . $e->getMessage());
    }

    return response()->json([
      'ok' => true,
      'message' => 'Client created',
      'data' => ['id' => $client->id],
    ], 201);
  }

  public function update(Request $request, Client $client)
  {
    $validated = $request->validate([
      'business_name' => ['required', 'string', 'max:160'],
      'contact_name' => ['nullable', 'string', 'max:160'],
      'email' => [
        'required',
        'email:rfc,dns',
        'max:190',
        Rule::unique('clients', 'email')->ignore($client->id),
      ],
      'website_url' => ['nullable', 'url', 'max:255'],
      'city' => ['nullable', 'string', 'max:120'],
      'country' => ['nullable', 'string', 'max:120'],
      'category_id' => ['nullable', 'integer', 'exists:categories,id'],
      'status' => ['required', Rule::in(['prospect', 'engaged', 'suppressed', 'paused', 'archived'])],
      'tags' => ['nullable', 'string', 'max:500'],
    ]);
    $client->update([
      'business_name' => $validated['business_name'],
      'contact_name' => $validated['contact_name'] ?? null,
      'email' => strtolower($validated['email']),
      'website_url' => $validated['website_url'] ?? null,
      'city' => $validated['city'] ?? null,
      'country' => $validated['country'] ?? null,
      'category_id' => $validated['category_id'] ?? null,
      'status' => $validated['status'],
    ]);

    $this->syncTags($client, $validated['tags'] ?? '');

    return response()->json([
      'ok' => true,
      'message' => 'Client updated',
    ]);
  }

  public function destroy(Client $client)
  {
    $client->delete(); // soft delete

    return response()->json([
      'ok' => true,
      'message' => 'Client deleted',
    ]);
  }

  public function notesIndex(Client $client)
  {
    $notes = $client->notes()
      ->with('user:id,name,email')
      ->latest('id')
      ->limit(30)
      ->get()
      ->map(function (ClientNote $n) {
        return [
          'id' => $n->id,
          'body' => $n->body,
          'user' => $n->user ? ['id' => $n->user->id, 'name' => $n->user->name] : null,
          'created_at' => optional($n->created_at)->toISOString(),
        ];
      })->values();

    return response()->json([
      'ok' => true,
      'data' => [
        'notes' => $notes,
      ],
    ]);
  }

  public function notesStore(Request $request, Client $client)
  {
    $validated = $request->validate([
      'body' => ['required', 'string', 'max:2000'],
    ]);

    $note = $client->notes()->create([
      'user_id' => $request->user()->id,
      'body' => $validated['body'],
    ]);

    return response()->json([
      'ok' => true,
      'message' => 'Note added',
      'data' => ['id' => $note->id],
    ], 201);
  }

  private function syncTags(Client $client, string $tagsCsv): void
  {
    $names = collect(explode(',', $tagsCsv))
      ->map(fn($t) => trim((string) $t))
      ->filter(fn($t) => $t !== '')
      ->map(fn($t) => mb_strtolower($t))
      ->unique()
      ->take(20)
      ->values();

    if ($names->isEmpty()) {
      $client->tags()->sync([]);
      return;
    }

    $ids = $names->map(function ($name) {
      return Tag::firstOrCreate(['name' => $name])->id;
    })->values()->all();

    $client->tags()->sync($ids);
  }
}
