<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

it('rejects users index for an unauthenticated request', function () {
    $this->getJson('/api/users')->assertStatus(401);
});

it('rejects users index for a non-admin user', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/users')->assertStatus(403);
});

it('rejects users index for a manager (not admin)', function () {
    Sanctum::actingAs(User::factory()->manager()->create());

    $this->getJson('/api/users')->assertStatus(403);
});

it('lists every user ordered by name for an admin', function () {
    $admin = User::factory()->admin()->create(['name' => 'Aaron Admin']);
    User::factory()->create(['name' => 'Charlie']);
    User::factory()->create(['name' => 'Bobbie']);
    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/users');

    $response->assertOk();
    $names = collect($response->json('data'))->pluck('name')->all();
    expect($names)->toBe(['Aaron Admin', 'Bobbie', 'Charlie']);
});

it('shows a single user by id for an admin', function () {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->create(['name' => 'Target', 'email' => 'target@example.com']);
    Sanctum::actingAs($admin);

    $response = $this->getJson("/api/users/{$target->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $target->id)
        ->assertJsonPath('data.email', 'target@example.com');
});

it('returns 404 when showing a non-existent user', function () {
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->getJson('/api/users/999999')->assertStatus(404);
});

it('creates a new user with a hashed password and the given role', function () {
    Sanctum::actingAs(User::factory()->admin()->create());

    $response = $this->postJson('/api/users', [
        'name' => 'New Manager',
        'email' => 'manager@example.com',
        'password' => 'secret-password',
        'role' => UserRole::Manager->value,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.email', 'manager@example.com')
        ->assertJsonPath('data.role', UserRole::Manager->value);

    $user = User::where('email', 'manager@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(UserRole::Manager)
        ->and(Hash::check('secret-password', $user->password))->toBeTrue();
});

it('rejects user creation when required fields are missing', function () {
    Sanctum::actingAs(User::factory()->admin()->create());

    $response = $this->postJson('/api/users', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
});

it('rejects user creation with a duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    Sanctum::actingAs(User::factory()->admin()->create());

    $response = $this->postJson('/api/users', [
        'name' => 'Other',
        'email' => 'taken@example.com',
        'password' => 'secret-password',
        'role' => UserRole::User->value,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

it('rejects user creation with an invalid role', function () {
    Sanctum::actingAs(User::factory()->admin()->create());

    $response = $this->postJson('/api/users', [
        'name' => 'Bad Role',
        'email' => 'badrole@example.com',
        'password' => 'secret-password',
        'role' => 'Hacker',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('role');
});

it('rejects user creation for a non-admin user', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/users', [
        'name' => 'Forbidden',
        'email' => 'forbidden@example.com',
        'password' => 'secret-password',
        'role' => UserRole::User->value,
    ]);

    $response->assertStatus(403);
});

it('updates every supported field on an existing user', function () {
    Sanctum::actingAs(User::factory()->admin()->create());
    $target = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
        'avatar_url' => null,
    ]);

    $response = $this->putJson("/api/users/{$target->id}", [
        'name' => 'New Name',
        'email' => 'new@example.com',
        'role' => UserRole::Manager->value,
        'avatarUrl' => 'https://cdn.example/avatar.png',
        'password' => 'new-secret-password',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'New Name')
        ->assertJsonPath('data.email', 'new@example.com')
        ->assertJsonPath('data.role', UserRole::Manager->value)
        ->assertJsonPath('data.avatarUrl', 'https://cdn.example/avatar.png');

    $fresh = $target->fresh();
    expect($fresh->role)->toBe(UserRole::Manager)
        ->and($fresh->avatar_url)->toBe('https://cdn.example/avatar.png')
        ->and(Hash::check('new-secret-password', $fresh->password))->toBeTrue();
});

it('allows clearing avatarUrl by sending null', function () {
    Sanctum::actingAs(User::factory()->admin()->create());
    $target = User::factory()->create(['avatar_url' => 'https://cdn.example/old.png']);

    $response = $this->putJson("/api/users/{$target->id}", [
        'avatarUrl' => null,
    ]);

    $response->assertOk()->assertJsonPath('data.avatarUrl', null);
    expect($target->fresh()->avatar_url)->toBeNull();
});

it('allows updating only a single field at a time', function () {
    Sanctum::actingAs(User::factory()->admin()->create());
    $target = User::factory()->create(['name' => 'Original']);

    $response = $this->putJson("/api/users/{$target->id}", ['name' => 'Renamed']);

    $response->assertOk()->assertJsonPath('data.name', 'Renamed');
    expect($target->fresh()->name)->toBe('Renamed');
});

it('allows keeping the same email when updating a user', function () {
    Sanctum::actingAs(User::factory()->admin()->create());
    $target = User::factory()->create(['email' => 'same@example.com']);

    $response = $this->putJson("/api/users/{$target->id}", [
        'email' => 'same@example.com',
        'name' => 'Renamed',
    ]);

    $response->assertOk();
});

it('rejects updating a user to an email already used by someone else', function () {
    Sanctum::actingAs(User::factory()->admin()->create());
    User::factory()->create(['email' => 'taken@example.com']);
    $target = User::factory()->create();

    $response = $this->putJson("/api/users/{$target->id}", [
        'email' => 'taken@example.com',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors('email');
});

it('returns 404 when updating a non-existent user', function () {
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->putJson('/api/users/999999', ['name' => 'Ghost'])->assertStatus(404);
});

it('rejects update for a non-admin user', function () {
    Sanctum::actingAs(User::factory()->create());
    $target = User::factory()->create();

    $this->putJson("/api/users/{$target->id}", ['name' => 'Hack'])->assertStatus(403);
});

it('deletes a user other than the authenticated admin', function () {
    Sanctum::actingAs(User::factory()->admin()->create());
    $target = User::factory()->create();

    $response = $this->deleteJson("/api/users/{$target->id}");

    $response->assertNoContent();
    expect(User::find($target->id))->toBeNull();
});

it('refuses to let an admin delete their own account', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->deleteJson("/api/users/{$admin->id}");

    $response->assertStatus(422)
        ->assertJson(['message' => "You can't delete your own account."]);
    expect(User::find($admin->id))->not->toBeNull();
});

it('returns 404 when deleting a non-existent user', function () {
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->deleteJson('/api/users/999999')->assertStatus(404);
});

it('rejects delete for a non-admin user', function () {
    Sanctum::actingAs(User::factory()->create());
    $target = User::factory()->create();

    $this->deleteJson("/api/users/{$target->id}")->assertStatus(403);
});
