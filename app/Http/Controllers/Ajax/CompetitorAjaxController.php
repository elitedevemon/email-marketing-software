<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Competitor;

class CompetitorAjaxController extends Controller
{
  public function index(Client $client)
  {
    $rows = $client->competitors()
      ->latest('id')
      ->get()
      ->map(function (Competitor $c) {
        $ins = (array) ($c->insights_json ?? []);
        return [
          'id' => $c->id,
          'client_id' => $c->client_id,
          'name' => $c->name,
          'website_url' => $c->website_url,
          'summary' => (string) ($ins['summary'] ?? ''),
          'notes' => (string) ($ins['notes'] ?? ''),
          'created_at' => optional($c->created_at)->toISOString(),
        ];
      })->values();

    return response()->json([
      'ok' => true,
      'data' => [
        'rows' => $rows,
      ],
    ]);
  }

  public function store(Request $request, Client $client)
  {
    $validated = $request->validate([
      'name' => ['required', 'string', 'max:160'],
      'website_url' => ['nullable', 'url', 'max:255'],
      'summary' => ['nullable', 'string', 'max:800'],
      'notes' => ['nullable', 'string', 'max:2000'],
    ]);

    $competitor = $client->competitors()->create([
      'name' => $validated['name'],
      'website_url' => $validated['website_url'] ?? null,
      'insights_json' => [
        'summary' => $validated['summary'] ?? '',
        'notes' => $validated['notes'] ?? '',
        'source' => 'manual',
        'captured_at' => now()->toISOString(),
      ],
    ]);

    return response()->json([
      'ok' => true,
      'message' => 'Competitor created',
      'data' => ['id' => $competitor->id],
    ], 201);
  }

  public function update(Request $request, Competitor $competitor)
  {
    $validated = $request->validate([
      'name' => ['required', 'string', 'max:160'],
      'website_url' => ['nullable', 'url', 'max:255'],
      'summary' => ['nullable', 'string', 'max:800'],
      'notes' => ['nullable', 'string', 'max:2000'],
    ]);

    $ins = (array) ($competitor->insights_json ?? []);
    $ins['summary'] = $validated['summary'] ?? '';
    $ins['notes'] = $validated['notes'] ?? '';
    $ins['source'] = $ins['source'] ?? 'manual';
    $ins['captured_at'] = now()->toISOString();

    $competitor->update([
      'name' => $validated['name'],
      'website_url' => $validated['website_url'] ?? null,
      'insights_json' => $ins,
    ]);

    return response()->json([
      'ok' => true,
      'message' => 'Competitor updated',
    ]);
  }

  public function destroy(Competitor $competitor)
  {
    $competitor->delete();

    return response()->json([
      'ok' => true,
      'message' => 'Competitor deleted',
    ]);
  }
}
