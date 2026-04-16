<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    /**
     * List users for the current tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $users = User::where('tenant_id', $tenant->id)
            ->select(['id', 'name', 'email', 'role', 'is_active', 'last_login_at', 'created_at'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($users);
    }

    /**
     * Create a new user for the current tenant.
     */
    public function store(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', 'in:admin,staff'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'tenant_id' => $tenant->id,
            'role' => $validated['role'],
        ]);

        return response()->json([
            'message' => 'User created.',
            'user' => $user->only(['id', 'name', 'email', 'role', 'is_active', 'created_at']),
        ], 201);
    }

    /**
     * Update an existing user (role, active status).
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($user->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        // Can't change the owner's role
        if ($user->role === 'owner' && $request->has('role')) {
            return response()->json(['message' => 'Cannot change the owner role.'], 422);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'role' => ['sometimes', 'string', 'in:admin,staff'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'User updated.',
            'user' => $user->only(['id', 'name', 'email', 'role', 'is_active', 'created_at']),
        ]);
    }

    /**
     * Delete a user from the tenant.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($user->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        // Can't delete yourself
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot delete yourself.'], 422);
        }

        // Can't delete the owner
        if ($user->role === 'owner') {
            return response()->json(['message' => 'Cannot delete the owner.'], 422);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }
}
