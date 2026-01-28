<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Validation\Rule;

class CategoryAjaxController extends Controller
{
  public function index(Request $request)
  {
    $q = trim((string) $request->query('q', ''));
    $status = (string) $request->query('status', 'all'); // all|active|inactive
    $sort = (string) $request->query('sort', 'sort_order'); // sort_order|name|created_at
    $dir = strtolower((string) $request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

    $allowedSorts = ['sort_order', 'name', 'created_at'];
    if (!in_array($sort, $allowedSorts, true)) $sort = 'sort_order';

    $query = Category::query();

    if ($q !== '') {
      $query->where('name', 'like', '%' . addcslashes($q, '%_') . '%');
    }

    if ($status === 'active') $query->where('is_active', true);
    if ($status === 'inactive') $query->where('is_active', false);

    // Stable ordering
    $query->orderBy($sort, $dir)->orderBy('id', 'desc');

    $p = $query->paginate(20)->withQueryString();

    return response()->json([
      'ok' => true,
      'data' => [
        'rows' => $p->getCollection()->map(function (Category $c) {
          return [
            'id' => $c->id,
            'name' => $c->name,
            'color' => $c->color,
            'is_active' => (bool) $c->is_active,
            'sort_order' => (int) $c->sort_order,
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
      'name' => ['required', 'string', 'max:120', 'unique:categories,name'],
      'color' => ['nullable', 'string', 'max:20'],
      'is_active' => ['nullable', 'boolean'],
      'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
    ]);

    $category = Category::create([
      'name' => $validated['name'],
      'color' => $validated['color'] ?? null,
      'is_active' => (bool) ($validated['is_active'] ?? true),
      'sort_order' => (int) ($validated['sort_order'] ?? 0),
    ]);

    return response()->json([
      'ok' => true,
      'message' => 'Category created',
      'data' => [
        'id' => $category->id,
      ],
    ], 201);
  }

  public function update(Request $request, Category $category)
  {
    $validated = $request->validate([
      'name' => [
        'required',
        'string',
        'max:120',
        Rule::unique('categories', 'name')->ignore($category->id),
      ],
      'color' => ['nullable', 'string', 'max:20'],
      'is_active' => ['nullable', 'boolean'],
      'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
    ]);

    $category->update([
      'name' => $validated['name'],
      'color' => $validated['color'] ?? null,
      'is_active' => (bool) ($validated['is_active'] ?? true),
      'sort_order' => (int) ($validated['sort_order'] ?? 0),
    ]);

    return response()->json([
      'ok' => true,
      'message' => 'Category updated',
    ]);
  }

  public function destroy(Category $category)
  {
    $category->delete();

    return response()->json([
      'ok' => true,
      'message' => 'Category deleted',
    ]);
  }
}
