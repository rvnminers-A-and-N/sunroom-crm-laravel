<?php

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('belongs to a user', function () {
    $user = User::factory()->create();
    $company = Company::factory()->for($user)->create();

    expect($company->user())->toBeInstanceOf(BelongsTo::class)
        ->and($company->user)->toBeInstanceOf(User::class)
        ->and($company->user->id)->toBe($user->id);
});

it('exposes contacts as a HasMany relation', function () {
    $company = Company::factory()->create();
    Contact::factory()->for($company)->count(3)->create();

    expect($company->contacts())->toBeInstanceOf(HasMany::class)
        ->and($company->contacts)->toHaveCount(3);
});

it('exposes deals as a HasMany relation', function () {
    $company = Company::factory()->create();
    Deal::factory()->for($company)->count(2)->create();

    expect($company->deals())->toBeInstanceOf(HasMany::class)
        ->and($company->deals)->toHaveCount(2);
});
