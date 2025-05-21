<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'fullName' => 'required|string',
                'username' => 'required|string|unique:users',
                'password' => 'required|string|min:6',
                'role' => 'required|in:admin,user',
            ]);

            // Split fullName into first_name and last_name
            $nameParts = explode(' ', $validated['fullName']);
            $firstName = $nameParts[0];
            $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

            $user = User::create([
                'username' => $validated['username'],
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $validated['username'] . '@example.com', // Temporary email
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token
            ], 201);

        } catch (ValidationException $e) {
            if (str_contains($e->getMessage(), 'username')) {
                return response()->json(['message' => 'Username taken'], 409);
            }
            return response()->json(['message' => 'Missing required fields'], 422);
        }
    }

    /**
     * Login user and create token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Logout user (Revoke the token).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Successfully logged out']);
    }
}
