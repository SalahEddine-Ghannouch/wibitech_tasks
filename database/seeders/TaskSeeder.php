<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some sample tasks for each user
        User::all()->each(function ($user) {
            Task::factory()->count(3)->create([
                'user_id' => $user->id,
                'status' => 'in_progress'
            ]);
            
            Task::factory()->count(2)->create([
                'user_id' => $user->id,
                'status' => 'done'
            ]);
        });
    }
} 