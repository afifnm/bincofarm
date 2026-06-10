<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        // Web SPA (request stateful dari domain sendiri): pakai session cookie
        if ($request->hasSession()) {
            if (!Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
                throw ValidationException::withMessages([
                    'email' => ['Email atau password salah.'],
                ]);
            }

            $request->session()->regenerate();

            ActivityLog::record('login', 'User login: ' . Auth::user()->name, Auth::user(), [], $request);

            return response()->json([
                'user' => Auth::user(),
            ]);
        }

        // Mobile / stateless: terbitkan Sanctum personal access token
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        Auth::setUser($user);

        $token = $user->createToken($credentials['device_name'] ?? 'mobile')->plainTextToken;

        ActivityLog::record('login', 'User login: ' . $user->name, $user, [], $request);

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            ActivityLog::record('logout', 'User logout: ' . $user->name, $user, [], $request);

            // Saat login via token, currentAccessToken() adalah PersonalAccessToken;
            // saat login via session (SPA) hanya TransientToken — jangan dihapus.
            $token = $user->currentAccessToken();
            if ($token instanceof PersonalAccessToken) {
                $token->delete();
            }
        }

        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(['message' => 'Berhasil logout.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }
}
