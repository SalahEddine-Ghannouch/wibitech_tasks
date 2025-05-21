<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $task->user);
        $this->assertEquals($user->id, $task->user->id);
    }

    /** @test */
    public function it_has_required_fillable_fields()
    {
        $task = new Task();
        $fillable = $task->getFillable();

        $this->assertContains('title', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('user_id', $fillable);
    }

    /** @test */
    public function it_validates_status_values()
    {
        $user = User::factory()->create();
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Task::create([
            'title' => 'Invalid Status Task',
            'description' => 'This should fail',
            'status' => 'invalid_status',
            'user_id' => $user->id
        ]);
    }
} 