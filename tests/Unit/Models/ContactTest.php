<?php

use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

it('exposes a fullName accessor combining first and last name', function () {
    $contact = Contact::factory()->create([
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
    ]);

    expect($contact->fullName)->toBe('Ada Lovelace');
});

it('casts last_contacted_at to a Carbon datetime', function () {
    $contact = Contact::factory()->create(['last_contacted_at' => '2026-01-15 10:30:00']);

    expect($contact->last_contacted_at)
        ->toBeInstanceOf(Carbon::class)
        ->and($contact->last_contacted_at->toDateString())->toBe('2026-01-15');
});

it('belongs to a user', function () {
    $user = User::factory()->create();
    $contact = Contact::factory()->for($user)->create();

    expect($contact->user())->toBeInstanceOf(BelongsTo::class)
        ->and($contact->user)->toBeInstanceOf(User::class)
        ->and($contact->user->id)->toBe($user->id);
});

it('belongs to a company', function () {
    $company = Company::factory()->create();
    $contact = Contact::factory()->for($company)->create();

    expect($contact->company())->toBeInstanceOf(BelongsTo::class)
        ->and($contact->company)->toBeInstanceOf(Company::class)
        ->and($contact->company->id)->toBe($company->id);
});

it('exposes deals as a HasMany relation', function () {
    $contact = Contact::factory()->create();
    Deal::factory()->for($contact)->count(2)->create();

    expect($contact->deals())->toBeInstanceOf(HasMany::class)
        ->and($contact->deals)->toHaveCount(2);
});

it('exposes activities as a HasMany relation', function () {
    $contact = Contact::factory()->create();
    Activity::factory()->for($contact)->count(3)->create();

    expect($contact->activities())->toBeInstanceOf(HasMany::class)
        ->and($contact->activities)->toHaveCount(3);
});

it('exposes tags as a BelongsToMany relation', function () {
    $contact = Contact::factory()->create();
    $tags = Tag::factory()->count(2)->create();
    $contact->tags()->attach($tags->pluck('id'));

    expect($contact->tags())->toBeInstanceOf(BelongsToMany::class)
        ->and($contact->fresh()->tags)->toHaveCount(2);
});
