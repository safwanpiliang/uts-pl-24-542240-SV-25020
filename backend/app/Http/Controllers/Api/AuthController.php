<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            Log::warning('login gagal', [
                'event' => 'auth.login_failed',
                'user_id' => $user?->id,
                'reason' => $user ? 'password_tidak_cocok' : 'akun_tidak_ditemukan',
            ]);

            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        Log::info('login berhasil', ['event' => 'auth.login', 'user_id' => $user->id]);

        return response()->json([
            'token' => $user->createToken('api')->plainTextToken,
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        Log::info('logout berhasil', ['event' => 'auth.logout', 'user_id' => $request->user()->id]);

        return response()->json(['message' => 'Logout berhasil.']);
    }
}
