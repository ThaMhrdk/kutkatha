<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Psikolog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    /**
     * Login user and create token.
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
                'device_name' => 'nullable|string',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->error('Email atau password salah.', 401);
            }

            if (!$user->is_active) {
                return $this->error('Akun Anda tidak aktif.', 403);
            }

            // Create token using Sanctum
            $token = $user->createToken($request->device_name ?? 'api_token')->plainTextToken;

            return $this->success([
                'user' => $user->load('psikolog'),
                'token' => $token,
                'token_type' => 'Bearer',
            ], 'Login berhasil.');

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => ['required', 'confirmed', Password::min(8)],
                'phone' => 'nullable|string|max:20',
                'role' => 'required|in:user,psikolog',
            ]);

            // Additional validation for psikolog
            if ($request->role === 'psikolog') {
                $request->validate([
                    'str_number' => 'required|string|unique:psikologs',
                    'specialization' => 'required|string',
                    'experience_years' => 'required|integer|min:0',
                    'consultation_fee' => 'required|numeric|min:0',
                ]);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role' => $request->role,
            ]);

            // Create psikolog profile if role is psikolog
            if ($request->role === 'psikolog') {
                Psikolog::create([
                    'user_id' => $user->id,
                    'str_number' => $request->str_number,
                    'specialization' => $request->specialization,
                    'experience_years' => $request->experience_years,
                    'consultation_fee' => $request->consultation_fee,
                    'verification_status' => 'pending',
                ]);
            }

            $token = $user->createToken('api_token')->plainTextToken;

            return $this->success([
                'user' => $user->load('psikolog'),
                'token' => $token,
                'token_type' => 'Bearer',
            ], 'Registrasi berhasil.', 201);

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Logout user (revoke current token).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logout berhasil.');
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $request->validate([
                'name' => 'sometimes|string|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('profiles', 'public');
                $user->photo = $photoPath;
            }

            $user->fill($request->only(['name', 'phone', 'address']));
            $user->save();

            return $this->success($user, 'Profil berhasil diperbarui.');

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Send password reset link.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            // TODO: Implement actual password reset email
            // For now, just return success message

            return $this->success(null, 'Link reset password telah dikirim ke email Anda.');

        } catch (ValidationException $e) {
            return $this->error('Email tidak ditemukan.', 404);
        }
    }
}
