<?php

use App\Enums\UserRole;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('registers a new user, returns a token, and persists the user with the User role', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'password' => 'secret-password',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role', 'avatarUrl', 'createdAt']])
        ->assertJsonPath('user.email', 'ada@example.com')
        ->assertJsonPath('user.role', UserRole::User->value);

    $user = User::where('email', 'ada@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(UserRole::User)
        ->and($user->tokens()->count())->toBe(1);
});

it('rejects registration when required fields are missing', function () {
    $response = $this->postJson('/api/auth/register', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('rejects registration when the email is already taken', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->postJson('/api/auth/register', [
        'name' => 'Other',
        'email' => 'taken@example.com',
        'password' => 'secret-password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

it('rejects registration when the password is shorter than 8 characters', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Tiny',
        'email' => 'tiny@example.com',
        'password' => 'short',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('password');
});

it('logs an existing user in and returns a token', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => 'secret-password',
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'login@example.com',
        'password' => 'secret-password',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'user' => ['id', 'email']])
        ->assertJsonPath('user.id', $user->id);

    expect($user->fresh()->tokens()->count())->toBe(1);
});

it('rejects login when required fields are missing', function () {
    $response = $this->postJson('/api/auth/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

it('rejects login with an invalid email address', function () {
    User::factory()->create(['email' => 'real@example.com', 'password' => 'secret-password']);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'unknown@example.com',
        'password' => 'secret-password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

it('rejects login when the password is wrong', function () {
    User::factory()->create(['email' => 'real@example.com', 'password' => 'secret-password']);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'real@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

it('logs a user out by revoking their current token', function () {
    $user = User::factory()->create();
    $plainToken = $user->createToken('api')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$plainToken)
        ->postJson('/api/auth/logout');

    $response->assertOk()->assertJson(['message' => 'Logged out successfully.']);
    expect($user->fresh()->tokens()->count())->toBe(0);
});

it('rejects logout when the request is unauthenticated', function () {
    $response = $this->postJson('/api/auth/logout');

    $response->assertStatus(401);
});

it('returns the authenticated user from the me endpoint', function () {
    $user = User::factory()->admin()->create([
        'name' => 'Grace Hopper',
        'email' => 'grace@example.com',
    ]);
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/auth/me');

    $response->assertOk()
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('email', 'grace@example.com')
        ->assertJsonPath('role', UserRole::Admin->value);
});

it('rejects me when the request is unauthenticated', function () {
    $response = $this->getJson('/api/auth/me');

    $response->assertStatus(401);
});
