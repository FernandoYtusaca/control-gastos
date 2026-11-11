<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SummaryController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        $month = $data['month'] ?? now()->month;
        $year = $data['year'] ?? now()->year;

        $byCategory = $request->user()->transactions()
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->whereYear('transactions.date', $year)
            ->whereMonth('transactions.date', $month)
            ->groupBy('categories.id', 'categories.name', 'categories.type')
            ->select(
                'categories.id as category_id',
                'categories.name',
                'categories.type',
                DB::raw('SUM(transactions.amount) as total')
            )
            ->orderByDesc('total')
            ->get();

        $income = $byCategory->where('type', 'income')->sum('total');
        $expense = $byCategory->where('type', 'expense')->sum('total');

        return [
            'month' => (int) $month,
            'year' => (int) $year,
            'total_income' => round($income, 2),
            'total_expense' => round($expense, 2),
            'balance' => round($income - $expense, 2),
            'by_category' => $byCategory,
        ];
    }
}