<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $adminToken;
    private $userToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        
        $this->adminToken = $this->admin->createToken('admin-token')->plainTextToken;
        $this->userToken = $this->user->createToken('user-token')->plainTextToken;
    }

    /** @test */
    public function admin_can_list_all_users()
    {
        User::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonCount(5); // 3 factory users + admin + regular user
    }

    /** @test */
    public function regular_user_cannot_list_users()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
            ->getJson('/api/users');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_register_new_user()
    {
        $userData = [
            'fullName' => 'New User',
            'username' => 'newuser',
            'password' => 'password123',
            'role' => 'user'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'username',
                    'first_name',
                    'last_name',
                    'email',
                    'role',
                    'created_at',
                    'updated_at'
                ],
                'token'
            ]);

        $this->assertDatabaseHas('users', [
            'username' => 'newuser',
            'role' => 'user'
        ]);
    }

    /** @test */
    public function user_registration_requires_unique_username_and_email()
    {
        $existingUser = User::factory()->create([
            'username' => 'existinguser'
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/register', [
                'fullName' => 'New User',
                'username' => 'existinguser',
                'password' => 'password123',
                'role' => 'user'
            ]);

        $response->assertStatus(409)
            ->assertJson(['message' => 'Username taken']);
    }
} 