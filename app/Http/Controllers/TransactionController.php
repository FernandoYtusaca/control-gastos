<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2000|max:2100',
            'category_id' => 'nullable|integer',
            'type' => ['nullable', Rule::in(['income', 'expense'])],
        ]);

        return $request->user()->transactions()
            ->with('category:id,name,type')
            ->when($request->year, fn ($q, $year) => $q->whereYear('date', $year))
            ->when($request->month, fn ($q, $month) => $q->whereMonth('date', $month))
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->type, fn ($q, $type) => $q->whereHas(
                'category', fn ($c) => $c->where('type', $type)
            ))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(15);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules($request));

        $transaction = $request->user()->transactions()->create($data);

        return response()->json($transaction->load('category:id,name,type'), 201);
    }

    public function show(Request $request, string $id)
    {
        return $request->user()->transactions()
            ->with('category:id,name,type')
            ->findOrFail($id);
    }

    public function update(Request $request, string $id)
    {
        $transaction = $request->user()->transactions()->findOrFail($id);

        $transaction->update($request->validate($this->rules($request)));

        return $transaction->load('category:id,name,type');
    }

    public function destroy(Request $request, string $id)
    {
        $request->user()->transactions()->findOrFail($id)->delete();

        return response()->noContent();
    }

    private function rules(Request $request): array
    {
        return [
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('user_id', $request->user()->id),
            ],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'description' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
        ];
    }
}