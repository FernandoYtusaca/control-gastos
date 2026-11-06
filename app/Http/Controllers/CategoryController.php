<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->categories()->orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('categories')->where(fn ($q) => $q
                    ->where('user_id', $request->user()->id)
                    ->where('type', $request->type)),
            ],
            'type' => ['required', Rule::in(['income', 'expense'])],
        ]);

        $category = $request->user()->categories()->create($data);

        return response()->json($category, 201);
    }

    public function show(Request $request, string $id)
    {
        return $request->user()->categories()->findOrFail($id);
    }

    public function update(Request $request, string $id)
    {
        $category = $request->user()->categories()->findOrFail($id);

        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('categories')
                    ->ignore($category->id)
                    ->where(fn ($q) => $q
                        ->where('user_id', $request->user()->id)
                        ->where('type', $category->type)),
            ],
        ]);

        $category->update($data);

        return $category;
    }

    public function destroy(Request $request, string $id)
    {
        $category = $request->user()->categories()->findOrFail($id);

        if ($category->transactions()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar una categoría con transacciones.',
            ], 409);
        }

        $category->delete();

        return response()->noContent();
    }
}