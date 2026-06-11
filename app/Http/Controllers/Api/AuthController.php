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
            'login'       => ['required_without:email', 'string'],
            'email'       => ['required_without:login', 'string'], // kompat lama: field email tetap diterima
            'password'    => ['required'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $identifier = $credentials['login'] ?? $credentials['email'];
        $user       = $this->findByEmailOrPhone($identifier);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Email/No. HP atau password salah.'],
            ]);
        }

        // Web SPA (request stateful dari domain sendiri): pakai session cookie
        if ($request->hasSession()) {
            Auth::guard('web')->login($user);
            $request->session()->regenerate();

            ActivityLog::record('login', 'User login: ' . $user->name, $user, [], $request);

            return response()->json([
                'user' => $user,
            ]);
        }

        // Mobile / stateless: terbitkan Sanctum personal access token
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

    /**
     * Cari user berdasarkan email atau no. HP.
     * No. HP dicocokkan tanpa memperhatikan format (spasi/strip/+62 vs 0).
     */
    private function findByEmailOrPhone(string $identifier): ?User
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return User::where('email', $identifier)->first();
        }

        $digits = preg_replace('/\D/', '', $identifier);
        if ($digits === '') {
            return null;
        }

        // Samakan prefix 62xxx menjadi 0xxx agar +62 812 cocok dengan 0812
        $normalized = preg_replace('/^62/', '0', $digits);

        return User::query()
            ->whereNotNull('phone')
            ->whereRaw(
                "REGEXP_REPLACE(REGEXP_REPLACE(phone, '[^0-9]', ''), '^62', '0') = ?",
                [$normalized]
            )
            ->first();
    }
}
