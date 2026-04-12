<?php

use App\Enums\UserRole;

it('exposes every expected role case with the right backing value', function () {
    expect(UserRole::User->value)->toBe('User')
        ->and(UserRole::Manager->value)->toBe('Manager')
        ->and(UserRole::Admin->value)->toBe('Admin');
});

it('lists exactly the three known roles', function () {
    expect(UserRole::cases())->toHaveCount(3)
        ->and(array_map(fn (UserRole $r) => $r->value, UserRole::cases()))
        ->toBe(['User', 'Manager', 'Admin']);
});

it('round-trips through the from() helper', function () {
    expect(UserRole::from('User'))->toBe(UserRole::User)
        ->and(UserRole::from('Manager'))->toBe(UserRole::Manager)
        ->and(UserRole::from('Admin'))->toBe(UserRole::Admin);
});
