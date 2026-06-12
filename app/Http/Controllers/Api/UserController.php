<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query();
        if ($request->search) {
            $query->where(function ($q) use ($request): void {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }
        $users = $query->orderBy('name')->paginate($request->per_page ?? 10);

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['nullable', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role'     => ['required', 'in:admin,inventory,pj_gh'],
            'phone'    => ['nullable', 'string', 'max:20'],
        ]);

        if (empty($validated['email']) && empty($validated['phone'])) {
            throw ValidationException::withMessages([
                'email' => ['Email atau No. HP harus diisi salah satu.'],
            ]);
        }

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        ActivityLog::record('create', "Tambah user: {$user->name}", $request->user(), [], $request);

        return response()->json(['user' => $user, 'message' => 'User berhasil ditambahkan.'], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['nullable', 'email', "unique:users,email,{$user->id}"],
            'role'     => ['required', 'in:admin,inventory,pj_gh'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        if (empty($validated['email']) && empty($validated['phone'])) {
            throw ValidationException::withMessages([
                'email' => ['Email atau No. HP harus diisi salah satu.'],
            ]);
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);
        ActivityLog::record('update', "Update user: {$user->name}", $request->user(), [], $request);

        return response()->json(['user' => $user, 'message' => 'User berhasil diperbarui.']);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Tidak bisa menghapus akun sendiri.'], 422);
        }

        ActivityLog::record('delete', "Hapus user: {$user->name}", $request->user(), [], $request);
        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus.']);
    }

    /**
     * Return the authenticated user profile.
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    /**
     * Update authenticated user's profile (name, phone, avatar).
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($validated);

        ActivityLog::record('update_profile', "Update profil user: {$user->name}", $user, [], $request);

        return response()->json(['user' => $user, 'message' => 'Profil berhasil diperbarui.']);
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password lama tidak sesuai.'],
            ]);
        }

        $user->update(['password' => Hash::make($validated['password'])]);

        ActivityLog::record('change_password', "Ganti password user: {$user->name}", $user, [], $request);

        return response()->json(['message' => 'Password berhasil diubah.']);
    }
}
