<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private function userWithCategories(): array
    {
        $user = User::factory()->create();
        $income = $user->categories()->create(['name' => 'Sueldo', 'type' => 'income']);
        $expense = $user->categories()->create(['name' => 'Comida', 'type' => 'expense']);

        return [$user, $income, $expense];
    }

    public function test_user_can_create_transaction(): void
    {
        [$user, , $expense] = $this->userWithCategories();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/transactions', [
                'category_id' => $expense->id,
                'amount' => 35.50,
                'description' => 'Almuerzo',
                'date' => '2026-11-05',
            ])
            ->assertCreated()
            ->assertJsonFragment(['description' => 'Almuerzo']);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'description' => 'Almuerzo',
        ]);
    }

    public function test_amount_must_be_positive(): void
    {
        [$user, , $expense] = $this->userWithCategories();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/transactions', [
                'category_id' => $expense->id,
                'amount' => -5,
                'date' => '2026-11-05',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('amount');
    }

    public function test_cannot_use_another_users_category(): void
    {
        [, , $expense] = $this->userWithCategories();
        $other = User::factory()->create();

        $this->actingAs($other, 'sanctum')
            ->postJson('/api/transactions', [
                'category_id' => $expense->id,
                'amount' => 10,
                'date' => '2026-11-05',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category_id');
    }

    public function test_can_filter_by_month_and_type(): void
    {
        [$user, $income, $expense] = $this->userWithCategories();

        $user->transactions()->create(['category_id' => $income->id, 'amount' => 2500, 'date' => '2026-11-01']);
        $user->transactions()->create(['category_id' => $expense->id, 'amount' => 35.50, 'date' => '2026-11-05']);
        $user->transactions()->create(['category_id' => $expense->id, 'amount' => 20, 'date' => '2026-10-20']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/transactions?month=11&year=2026&type=expense')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}