<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_calculates_totals_for_the_month(): void
    {
        $user = User::factory()->create();
        $income = $user->categories()->create(['name' => 'Sueldo', 'type' => 'income']);
        $expense = $user->categories()->create(['name' => 'Comida', 'type' => 'expense']);

        $user->transactions()->create(['category_id' => $income->id, 'amount' => 2500, 'date' => '2026-11-01']);
        $user->transactions()->create(['category_id' => $expense->id, 'amount' => 35.50, 'date' => '2026-11-05']);
        $user->transactions()->create(['category_id' => $expense->id, 'amount' => 20, 'date' => '2026-10-20']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/summary?month=11&year=2026')
            ->assertOk()
            ->assertJson([
                'total_income' => 2500.0,
                'total_expense' => 35.5,
                'balance' => 2464.5,
            ]);
    }

    public function test_summary_rejects_invalid_month(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/summary?month=13')
            ->assertUnprocessable();
    }
}