<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes in this file are automatically prefixed with '/api'
| and are protected by the 'api' middleware group.
| These routes are stateless and return JSON responses.
|
*/

// Public routes - No authentication required
Route::post('/register', [AuthController::class, 'register']); // Register new user
Route::post('/login', [AuthController::class, 'login']);      // Login and get token

// Protected routes - Require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']); // Logout and invalidate token
    
    // Task routes accessible by all authenticated users
    Route::get('/tasks', [TaskController::class, 'index']);           // List tasks (filtered by role)
    Route::get('/tasks/{task}', [TaskController::class, 'show']);     // Get specific task
    Route::put('/tasks/{task}', [TaskController::class, 'update']);   // Update task

    // Admin only routes - Require admin role
    Route::middleware('role:admin')->group(function () {
        Route::post('/tasks', [TaskController::class, 'store']);      // Create new task
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy']); // Delete task
        Route::get('/users', [UserController::class, 'index']);       // List all users
    });
});
