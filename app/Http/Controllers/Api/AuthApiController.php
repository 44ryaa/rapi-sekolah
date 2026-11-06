<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthApiController extends Controller
{
    /**
     * POST /api/login
     * Body: { username, password }
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Username atau password salah.'], 401);
        }

        $user->load('role');
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userResource($user),
        ]);
    }

    /**
     * POST /api/logout
     * Header: Authorization: Bearer {token}
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil logout.']);
    }

    /**
     * GET /api/me
     */
    public function me(Request $request)
    {
        return response()->json(['user' => $this->userResource($request->user()->load('role'))]);
    }

    /**
     * PUT /api/me
     * Body: { fullname?, phone_number?, password?, password_confirmation? }
     */
    public function updateMe(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'fullname' => 'sometimes|string|max:100',
            'phone_number' => 'sometimes|string|max:20',
            'password' => 'sometimes|string|min:6|confirmed',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json(['user' => $this->userResource($user->fresh('role'))]);
    }

    private function userResource(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'fullname' => $user->fullname,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'role' => $user->role?->role_name ?? $user->role?->name ?? null,
            'role_id' => $user->role_id,
        ];
    }
}
