<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection(User::orderBy('name')->get());
    }

    public function show(int $id): UserResource
    {
        return new UserResource(User::findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => UserRole::from($validated['role']),
        ]);

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $id): UserResource
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'role' => ['sometimes', Rule::in(array_column(UserRole::cases(), 'value'))],
            'avatarUrl' => ['sometimes', 'nullable', 'string', 'max:500'],
            'password' => ['sometimes', 'string', 'min:8'],
        ]);

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        if (isset($validated['role'])) {
            $user->role = UserRole::from($validated['role']);
        }
        if (array_key_exists('avatarUrl', $validated)) {
            $user->avatar_url = $validated['avatarUrl'];
        }
        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return new UserResource($user);
    }

    public function destroy(int $id): JsonResponse
    {
        if ((int) auth()->id() === $id) {
            return response()->json(['message' => "You can't delete your own account."], 422);
        }

        User::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
