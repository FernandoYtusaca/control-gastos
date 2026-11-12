<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return [
            'name' => 'Test',
            'email' => 'test@mail.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ];
    }

    public function test_user_can_register(): void
    {
        $this->postJson('/api/register', $this->payload())
            ->assertCreated()
            ->assertJsonStructure(['user', 'token']);

        $this->assertDatabaseHas('users', ['email' => 'test@mail.com']);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'test@mail.com']);

        $this->postJson('/api/register', $this->payload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_user_can_login(): void
    {
        User::factory()->create(['email' => 'test@mail.com', 'password' => '12345678']);

        $this->postJson('/api/login', ['email' => 'test@mail.com', 'password' => '12345678'])
            ->assertOk()
            ->assertJsonStructure(['user', 'token']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'test@mail.com', 'password' => '12345678']);

        $this->postJson('/api/login', ['email' => 'test@mail.com', 'password' => 'incorrecta'])
            ->assertUnprocessable();
    }

    public function test_protected_route_requires_token(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }
}