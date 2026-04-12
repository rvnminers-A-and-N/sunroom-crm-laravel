<?php

use App\Enums\ActivityType;

it('exposes every activity type case with the right backing value', function () {
    expect(ActivityType::Note->value)->toBe('Note')
        ->and(ActivityType::Call->value)->toBe('Call')
        ->and(ActivityType::Email->value)->toBe('Email')
        ->and(ActivityType::Meeting->value)->toBe('Meeting')
        ->and(ActivityType::Task->value)->toBe('Task');
});

it('lists exactly the five known activity types', function () {
    expect(ActivityType::cases())->toHaveCount(5)
        ->and(array_map(fn (ActivityType $t) => $t->value, ActivityType::cases()))
        ->toBe(['Note', 'Call', 'Email', 'Meeting', 'Task']);
});

it('round-trips through the from() helper', function () {
    foreach (['Note', 'Call', 'Email', 'Meeting', 'Task'] as $value) {
        expect(ActivityType::from($value)->value)->toBe($value);
    }
});
