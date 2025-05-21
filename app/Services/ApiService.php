<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ApiService
{
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('app.url');
        $this->token = session('api_token');
    }

    protected function getHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
            'X-CSRF-TOKEN' => csrf_token()
        ];
    }

    protected function validateRequest($data, $rules)
    {
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function login($credentials)
    {
        $validated = $this->validateRequest($credentials, [
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        $response = Http::post($this->baseUrl . '/api/login', $validated);
        
        if ($response->successful()) {
            $token = $response->json('token');
            session(['api_token' => $token]);
            Cache::put('user_token', $token, now()->addHours(24));
        }
        
        return $response;
    }

    public function register($data)
    {
        $validated = $this->validateRequest($data, [
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,user'
        ]);

        $response = Http::post($this->baseUrl . '/api/register', $validated);
        
        if ($response->successful()) {
            $token = $response->json('token');
            session(['api_token' => $token]);
            Cache::put('user_token', $token, now()->addHours(24));
        }
        
        return $response;
    }

    public function getTasks()
    {
        return Http::withHeaders($this->getHeaders())
            ->get($this->baseUrl . '/api/tasks');
    }

    public function createTask($data)
    {
        $validated = $this->validateRequest($data, [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:in_progress,done',
            'assignedTo' => 'required|exists:users,username'
        ]);

        return Http::withHeaders($this->getHeaders())
            ->post($this->baseUrl . '/api/tasks', $validated);
    }

    public function updateTask($id, $data)
    {
        $validated = $this->validateRequest($data, [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:in_progress,done'
        ]);

        return Http::withHeaders($this->getHeaders())
            ->put($this->baseUrl . '/api/tasks/' . $id, $validated);
    }

    public function deleteTask($id)
    {
        return Http::withHeaders($this->getHeaders())
            ->delete($this->baseUrl . '/api/tasks/' . $id);
    }

    public function getUsers()
    {
        return Http::withHeaders($this->getHeaders())
            ->get($this->baseUrl . '/api/users');
    }

    public function logout()
    {
        $response = Http::withHeaders($this->getHeaders())
            ->post($this->baseUrl . '/api/logout');

        if ($response->successful()) {
            session()->forget('api_token');
            Cache::forget('user_token');
        }

        return $response;
    }
}