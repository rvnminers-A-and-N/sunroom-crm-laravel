<?php

use App\Enums\UserRole;
use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

it('reports admin status correctly via isAdmin', function () {
    expect(User::factory()->admin()->create()->isAdmin())->toBeTrue()
        ->and(User::factory()->manager()->create()->isAdmin())->toBeFalse()
        ->and(User::factory()->create()->isAdmin())->toBeFalse();
});

it('declares the expected casts', function () {
    $user = User::factory()->create();

    expect($user->getCasts())
        ->toMatchArray([
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ]);
});

it('hashes the password through the hashed cast', function () {
    $user = User::factory()->create(['password' => 'plain-secret']);

    expect($user->password)->not->toBe('plain-secret')
        ->and(Hash::check('plain-secret', $user->password))->toBeTrue();
});

it('casts email_verified_at to a Carbon instance', function () {
    $user = User::factory()->create();

    expect($user->email_verified_at)->toBeInstanceOf(Carbon::class);
});

it('casts the role attribute to a UserRole enum', function () {
    $user = User::factory()->admin()->create();

    expect($user->role)->toBeInstanceOf(UserRole::class)
        ->and($user->role)->toBe(UserRole::Admin);
});

it('exposes companies as a HasMany relation', function () {
    $user = User::factory()->create();
    Company::factory()->for($user)->count(2)->create();

    expect($user->companies())->toBeInstanceOf(HasMany::class)
        ->and($user->companies)->toHaveCount(2);
});

it('exposes contacts as a HasMany relation', function () {
    $user = User::factory()->create();
    Contact::factory()->for($user)->count(3)->create();

    expect($user->contacts())->toBeInstanceOf(HasMany::class)
        ->and($user->contacts)->toHaveCount(3);
});

it('exposes deals as a HasMany relation', function () {
    $user = User::factory()->create();
    Deal::factory()->for($user)->count(2)->create();

    expect($user->deals())->toBeInstanceOf(HasMany::class)
        ->and($user->deals)->toHaveCount(2);
});

it('exposes activities as a HasMany relation', function () {
    $user = User::factory()->create();
    Activity::factory()->for($user)->count(4)->create();

    expect($user->activities())->toBeInstanceOf(HasMany::class)
        ->and($user->activities)->toHaveCount(4);
});
