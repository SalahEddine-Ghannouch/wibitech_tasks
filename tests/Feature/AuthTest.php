<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'testuser',
            'password' => 'password'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'username',
                    'first_name',
                    'last_name',
                    'email',
                    'role'
                ]
            ]);
    }

    /** @test */
    public function user_cannot_login_with_incorrect_credentials()
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'testuser',
            'password' => 'wrong_password'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function user_cannot_access_protected_route_without_token()
    {
        $response = $this->getJson('/api/tasks');

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_access_protected_route_with_valid_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/tasks');

        $response->assertStatus(200);
    }
} 