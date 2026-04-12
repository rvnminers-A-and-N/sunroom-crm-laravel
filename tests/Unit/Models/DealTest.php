<?php

use App\Enums\DealStage;
use App\Models\Activity;
use App\Models\AiInsight;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

it('declares the expected casts', function () {
    expect((new Deal)->getCasts())
        ->toMatchArray([
            'value' => 'decimal:2',
            'stage' => DealStage::class,
            'expected_close_date' => 'date',
            'closed_at' => 'datetime',
        ]);
});

it('casts value to a 2-decimal string', function () {
    $deal = Deal::factory()->create(['value' => 1234.5]);

    expect($deal->value)->toBe('1234.50');
});

it('casts stage to a DealStage enum', function () {
    $deal = Deal::factory()->create(['stage' => DealStage::Won]);

    expect($deal->stage)->toBeInstanceOf(DealStage::class)
        ->and($deal->stage)->toBe(DealStage::Won);
});

it('casts expected_close_date to a date and closed_at to a datetime', function () {
    $deal = Deal::factory()->create([
        'expected_close_date' => '2026-06-30',
        'closed_at' => '2026-06-15 14:00:00',
    ]);

    expect($deal->expected_close_date)->toBeInstanceOf(Carbon::class)
        ->and($deal->expected_close_date->toDateString())->toBe('2026-06-30')
        ->and($deal->closed_at)->toBeInstanceOf(Carbon::class)
        ->and($deal->closed_at->toDateTimeString())->toBe('2026-06-15 14:00:00');
});

it('belongs to a user', function () {
    $user = User::factory()->create();
    $deal = Deal::factory()->for($user)->create();

    expect($deal->user())->toBeInstanceOf(BelongsTo::class)
        ->and($deal->user)->toBeInstanceOf(User::class)
        ->and($deal->user->id)->toBe($user->id);
});

it('belongs to a contact', function () {
    $contact = Contact::factory()->create();
    $deal = Deal::factory()->for($contact)->create();

    expect($deal->contact())->toBeInstanceOf(BelongsTo::class)
        ->and($deal->contact)->toBeInstanceOf(Contact::class)
        ->and($deal->contact->id)->toBe($contact->id);
});

it('belongs to a company', function () {
    $company = Company::factory()->create();
    $deal = Deal::factory()->for($company)->create();

    expect($deal->company())->toBeInstanceOf(BelongsTo::class)
        ->and($deal->company)->toBeInstanceOf(Company::class)
        ->and($deal->company->id)->toBe($company->id);
});

it('exposes activities as a HasMany relation', function () {
    $deal = Deal::factory()->create();
    Activity::factory()->for($deal)->count(2)->create();

    expect($deal->activities())->toBeInstanceOf(HasMany::class)
        ->and($deal->activities)->toHaveCount(2);
});

it('exposes aiInsights as a HasMany relation', function () {
    $deal = Deal::factory()->create();
    AiInsight::factory()->for($deal)->count(3)->create();

    expect($deal->aiInsights())->toBeInstanceOf(HasMany::class)
        ->and($deal->aiInsights)->toHaveCount(3);
});
