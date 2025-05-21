<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Display a listing of the tasks.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $tasks = Task::with('user')->get();
        } else {
            $tasks = $user->tasks;
        }

        return response()->json($tasks);
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request)
    {
        // 401 is handled by Laravel's auth middleware

        // 403 - Requires admin role
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Requires admin role'], 403);
        }

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'status' => ['required', Rule::in(['in_progress', 'done'])],
                'assignedTo' => 'required|exists:users,username',
            ]);

            $assignedUser = User::where('username', $validated['assignedTo'])->first();

            $task = Task::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'status' => $validated['status'],
                'user_id' => $assignedUser->id,
            ]);

            // 201 - Task created
            return response()->json($task, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // 422 - Missing fields
            return response()->json([
                'message' => 'Missing fields',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && $task->user_id !== $user->id) {
            return response()->json(['message' => 'Not allowed to view this task'], 403);
        }

        return response()->json($task->load('user'));
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, Task $task)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && $task->user_id !== $user->id) {
            return response()->json(['message' => 'Not allowed to update this task'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'status' => ['sometimes', 'required', Rule::in(['in_progress', 'done'])],
            'assignedTo' => 'sometimes|required|exists:users,username',
        ]);

        if (isset($validated['assignedTo'])) {
            $assignedUser = User::where('username', $validated['assignedTo'])->first();
            $validated['user_id'] = $assignedUser->id;
            unset($validated['assignedTo']);
        }

        $task->update($validated);

        return response()->json($task->load('user'));
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();
        return response()->noContent(204);
    }
}
