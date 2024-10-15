<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Register User  
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'preferences' => 'nullable|array',
            'preferences.sources' => 'nullable|array',
            'preferences.categories' => 'nullable|array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'preferences' => $request->preferences ? json_encode($request->preferences) : null,
        ]);

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'preferences' => json_decode($user->preferences),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'id' => $user->id,
            ]
        ], 201);
    }
    // Login User  
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $userResponse = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];

        return response()->json(['message' => 'Login successful.', 'user' => $userResponse]);
    }
    public function logout()
    {
        return response()->json(['message' => 'Logout successful.']);
    }
}
