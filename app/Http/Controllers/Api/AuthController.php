<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username'     => 'required|string|max:255|unique:users,username',
            'email'        => 'nullable|email|max:255|unique:users,email',
            'phone_number' => 'required|string|max:20',
            'password'     => 'required|string|min:6',
        ]);

        $user = User::create([
            'username'     => $validated['username'],
            'email'        => $validated['email'] ?? null,
            'password'     => $validated['password'],
            'phone_number' => $validated['phone_number'],
        ]);

        return response()->json([
            'message' => 'Account created successfully.',
            'data'    => [
                'id'           => $user->id,
                'username'     => $user->username,
                'email'        => $user->email,
                'phone_number' => $user->phone_number,
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $validated['username'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid username or password.',
            ], 401);
        }

        $token = Str::random(60);
        $user->update(['api_token' => hash('sha256', $token)]);

        return response()->json([
            'message' => 'Login successful.',
            'data'    => [
                'id'           => $user->id,
                'username'     => $user->username,
                'email'        => $user->email,
                'phone_number' => $user->phone_number,
                'token'        => $token,
            ],
        ]);
    }
}
