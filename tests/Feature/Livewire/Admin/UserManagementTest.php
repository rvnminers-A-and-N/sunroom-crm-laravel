<?php

use App\Enums\UserRole;
use App\Livewire\Admin\UserManagement;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

it('redirects to login when not authenticated', function () {
    $this->get(route('admin.users'))->assertRedirect('/login');
});

it('aborts a non-admin user with 403 when mounting', function () {
    actingAsUser();

    Livewire::test(UserManagement::class)
        ->assertStatus(403);
});

it('renders the listing for an admin', function () {
    actingAsAdmin();
    User::factory()->create(['name' => 'Alice']);
    User::factory()->create(['name' => 'Bob']);

    Livewire::test(UserManagement::class)
        ->assertStatus(200)
        ->assertSee('Alice')
        ->assertSee('Bob');
});

it('searches users by name and email', function () {
    actingAsAdmin(['name' => 'Admin']);
    User::factory()->create(['name' => 'Alice', 'email' => 'alice@example.com']);
    User::factory()->create(['name' => 'Bob', 'email' => 'bob@example.com']);

    Livewire::test(UserManagement::class)
        ->set('search', 'Alice')
        ->assertSee('Alice')
        ->assertDontSee('bob@example.com');

    Livewire::test(UserManagement::class)
        ->set('search', 'bob@example.com')
        ->assertSee('Bob')
        ->assertDontSee('Alice');
});

it('opens the create modal with a clean form', function () {
    actingAsAdmin();

    Livewire::test(UserManagement::class)
        ->set('name', 'Stale')
        ->call('create')
        ->assertSet('showForm', true)
        ->assertSet('name', '')
        ->assertSet('role', 'User')
        ->assertSet('editingUserId', null);
});

it('creates a new user', function () {
    actingAsAdmin();

    Livewire::test(UserManagement::class)
        ->call('create')
        ->set('name', 'New Person')
        ->set('email', 'new@example.com')
        ->set('password', 'pass-1234')
        ->set('role', 'Manager')
        ->call('save')
        ->assertSet('showForm', false)
        ->assertHasNoErrors();

    $user = User::firstWhere('email', 'new@example.com');
    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('New Person')
        ->and($user->role)->toBe(UserRole::Manager)
        ->and(Hash::check('pass-1234', $user->password))->toBeTrue();
});

it('validates required fields and password length on create', function () {
    actingAsAdmin();

    Livewire::test(UserManagement::class)
        ->call('create')
        ->set('password', 'short')
        ->call('save')
        ->assertHasErrors([
            'name' => 'required',
            'email' => 'required',
            'password' => 'min',
        ]);
});

it('rejects a duplicate email on create', function () {
    actingAsAdmin();
    User::factory()->create(['email' => 'taken@example.com']);

    Livewire::test(UserManagement::class)
        ->set('name', 'X')
        ->set('email', 'taken@example.com')
        ->set('password', 'pass-1234')
        ->call('save')
        ->assertHasErrors(['email' => 'unique']);
});

it('opens the edit modal pre-populated', function () {
    actingAsAdmin();
    $user = User::factory()->create([
        'name' => 'Editable',
        'email' => 'edit@example.com',
        'role' => UserRole::Manager,
    ]);

    Livewire::test(UserManagement::class)
        ->call('edit', $user->id)
        ->assertSet('showForm', true)
        ->assertSet('editingUserId', $user->id)
        ->assertSet('name', 'Editable')
        ->assertSet('email', 'edit@example.com')
        ->assertSet('role', 'Manager')
        ->assertSet('password', '');
});

it('updates a user without changing the password when password is empty', function () {
    $admin = actingAsAdmin();
    $user = User::factory()->create(['name' => 'Old', 'password' => Hash::make('keep-this')]);

    Livewire::test(UserManagement::class)
        ->call('edit', $user->id)
        ->set('name', 'Renamed')
        ->call('save')
        ->assertSet('showForm', false)
        ->assertHasNoErrors();

    $fresh = $user->fresh();
    expect($fresh->name)->toBe('Renamed')
        ->and(Hash::check('keep-this', $fresh->password))->toBeTrue();
});

it('updates a user password when a new one is provided', function () {
    actingAsAdmin();
    $user = User::factory()->create(['password' => Hash::make('old-pass')]);

    Livewire::test(UserManagement::class)
        ->call('edit', $user->id)
        ->set('password', 'brand-new-pw')
        ->call('save')
        ->assertHasNoErrors();

    expect(Hash::check('brand-new-pw', $user->fresh()->password))->toBeTrue();
});

it('validates the password length when updating with a non-empty password', function () {
    actingAsAdmin();
    $user = User::factory()->create();

    Livewire::test(UserManagement::class)
        ->call('edit', $user->id)
        ->set('password', 'short')
        ->call('save')
        ->assertHasErrors(['password' => 'min']);
});

it('opens the delete confirmation modal', function () {
    actingAsAdmin();
    $user = User::factory()->create();

    Livewire::test(UserManagement::class)
        ->call('confirmDelete', $user->id)
        ->assertSet('showDeleteConfirm', true)
        ->assertSet('deletingUserId', $user->id);
});

it('deletes a user', function () {
    actingAsAdmin();
    $user = User::factory()->create();

    Livewire::test(UserManagement::class)
        ->call('confirmDelete', $user->id)
        ->call('delete')
        ->assertSet('showDeleteConfirm', false)
        ->assertSet('deletingUserId', null);

    expect(User::find($user->id))->toBeNull();
});

it('refuses to delete the currently logged-in admin', function () {
    $admin = actingAsAdmin();

    Livewire::test(UserManagement::class)
        ->set('deletingUserId', $admin->id)
        ->call('delete')
        ->assertSet('showDeleteConfirm', false)
        ->assertSet('deletingUserId', null);

    expect(User::find($admin->id))->not->toBeNull();
});
