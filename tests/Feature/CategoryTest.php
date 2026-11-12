<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_category(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/categories', ['name' => 'Comida', 'type' => 'expense'])
            ->assertCreated()
            ->assertJsonFragment(['name' => 'Comida']);

        $this->assertDatabaseHas('categories', ['user_id' => $user->id, 'name' => 'Comida']);
    }

    public function test_cannot_create_duplicate_category(): void
    {
        $user = User::factory()->create();
        $user->categories()->create(['name' => 'Sueldo', 'type' => 'income']);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/categories', ['name' => 'Sueldo', 'type' => 'income'])
            ->assertUnprocessable();
    }

    public function test_user_cannot_see_another_users_category(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $category = $owner->categories()->create(['name' => 'Comida', 'type' => 'expense']);

        $this->actingAs($other, 'sanctum')
            ->getJson("/api/categories/{$category->id}")
            ->assertNotFound();
    }

    public function test_cannot_delete_category_with_transactions(): void
    {
        $user = User::factory()->create();
        $category = $user->categories()->create(['name' => 'Comida', 'type' => 'expense']);
        $user->transactions()->create([
            'category_id' => $category->id,
            'amount' => 10,
            'date' => '2026-11-05',
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/categories/{$category->id}")
            ->assertStatus(409);
    }
}