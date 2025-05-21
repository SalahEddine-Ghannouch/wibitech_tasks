<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskTest extends TestCase
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
    public function admin_can_create_task()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/tasks', [
                'title' => 'Test Task',
                'description' => 'Test Description',
                'status' => 'in_progress',
                'assignedTo' => $this->user->username
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'title',
                'description',
                'status',
                'user_id'
            ]);
    }

    /** @test */
    public function regular_user_cannot_create_task()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
            ->postJson('/api/tasks', [
                'title' => 'Test Task',
                'description' => 'Test Description',
                'status' => 'in_progress',
                'assignedTo' => $this->user->username
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_see_all_tasks()
    {
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function regular_user_can_only_see_their_tasks()
    {
        Task::factory()->count(2)->create(['user_id' => $this->user->id]);
        Task::factory()->count(3)->create(['user_id' => $this->admin->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
            ->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    /** @test */
    public function admin_can_update_any_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->putJson("/api/tasks/{$task->id}", [
                'title' => 'Updated Title',
                'status' => 'done'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'title' => 'Updated Title',
                'status' => 'done'
            ]);
    }

    /** @test */
    public function regular_user_can_only_update_their_tasks()
    {
        $task = Task::factory()->create(['user_id' => $this->admin->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
            ->putJson("/api/tasks/{$task->id}", [
                'title' => 'Updated Title'
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_delete_any_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function regular_user_cannot_delete_tasks()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function task_creation_requires_all_fields()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/tasks', [
                'title' => 'Test Task'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description', 'status', 'assignedTo']);
    }
} 