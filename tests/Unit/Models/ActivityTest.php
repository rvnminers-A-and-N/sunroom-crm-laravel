<?php

use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

it('declares the expected casts', function () {
    expect((new Activity)->getCasts())
        ->toMatchArray([
            'type' => ActivityType::class,
            'occurred_at' => 'datetime',
        ]);
});

it('casts type to an ActivityType enum', function () {
    $activity = Activity::factory()->create(['type' => ActivityType::Call]);

    expect($activity->type)->toBeInstanceOf(ActivityType::class)
        ->and($activity->type)->toBe(ActivityType::Call);
});

it('casts occurred_at to a Carbon datetime', function () {
    $activity = Activity::factory()->create(['occurred_at' => '2026-02-10 09:15:00']);

    expect($activity->occurred_at)->toBeInstanceOf(Carbon::class)
        ->and($activity->occurred_at->toDateTimeString())->toBe('2026-02-10 09:15:00');
});

it('belongs to a user', function () {
    $user = User::factory()->create();
    $activity = Activity::factory()->for($user)->create();

    expect($activity->user())->toBeInstanceOf(BelongsTo::class)
        ->and($activity->user)->toBeInstanceOf(User::class)
        ->and($activity->user->id)->toBe($user->id);
});

it('belongs to a contact', function () {
    $contact = Contact::factory()->create();
    $activity = Activity::factory()->for($contact)->create();

    expect($activity->contact())->toBeInstanceOf(BelongsTo::class)
        ->and($activity->contact)->toBeInstanceOf(Contact::class)
        ->and($activity->contact->id)->toBe($contact->id);
});

it('belongs to a deal', function () {
    $deal = Deal::factory()->create();
    $activity = Activity::factory()->for($deal)->create();

    expect($activity->deal())->toBeInstanceOf(BelongsTo::class)
        ->and($activity->deal)->toBeInstanceOf(Deal::class)
        ->and($activity->deal->id)->toBe($deal->id);
});
