<?php

use App\Enums\UserRole;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

it('serializes a user with the role enum value and all base fields', function () {
    $user = User::factory()->admin()->create([
        'name' => 'Grace Hopper',
        'email' => 'grace@example.com',
        'avatar_url' => 'https://cdn.example/avatars/grace.png',
    ]);

    $array = (new UserResource($user))->toArray(Request::create('/'));

    expect($array)
        ->toHaveKeys(['id', 'name', 'email', 'role', 'avatarUrl', 'createdAt'])
        ->and($array['name'])->toBe('Grace Hopper')
        ->and($array['email'])->toBe('grace@example.com')
        ->and($array['role'])->toBe(UserRole::Admin->value)
        ->and($array['avatarUrl'])->toBe('https://cdn.example/avatars/grace.png')
        ->and($array['createdAt'])->toBeString();
});

it('serializes a user with a null avatarUrl', function () {
    $user = User::factory()->create(['avatar_url' => null]);

    $array = (new UserResource($user))->toArray(Request::create('/'));

    expect($array['avatarUrl'])->toBeNull();
});
