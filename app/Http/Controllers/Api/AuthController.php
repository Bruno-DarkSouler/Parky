<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RefreshToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado'
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'error' => true,
                'code' => 'UNAUTHORIZED',
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        $user = auth('api')->user();

        // Generate long-lived refresh token (7 days)
        $refreshToken = RefreshToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', Str::random(60)),
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json([
            'access_token' => $token,
            'refresh_token' => $refreshToken->token,
            'user' => $user
        ], 200);
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $refreshToken = RefreshToken::active()->where('token', $request->refresh_token)->first();

        if (!$refreshToken) {
            return response()->json([
                'error' => true,
                'code' => 'INVALID_REFRESH_TOKEN',
                'message' => 'Token inválido o expirado'
            ], 401);
        }

        // Generate new access token
        $token = auth('api')->login($refreshToken->user);

        return response()->json([
            'access_token' => $token
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $refreshToken = RefreshToken::active()
            ->where('token', $request->refresh_token)
            ->where('user_id', auth('api')->id())
            ->first();

        if ($refreshToken) {
            $refreshToken->update(['revoked_at' => now()]);
        }

        auth('api')->logout();

        return response()->json([
            'success' => true
        ], 200);
    }
}
