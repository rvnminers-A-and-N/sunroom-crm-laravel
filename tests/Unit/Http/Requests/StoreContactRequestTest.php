<?php

use App\Http\Requests\StoreContactRequest;
use App\Models\Company;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

function storeContactValidator(array $overrides = []): Illuminate\Validation\Validator
{
    $defaults = [
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'phone' => '555-0100',
        'title' => 'Engineer',
        'notes' => 'Met at conference',
    ];

    return Validator::make(array_merge($defaults, $overrides), (new StoreContactRequest)->rules());
}

it('authorizes the request unconditionally', function () {
    expect((new StoreContactRequest)->authorize())->toBeTrue();
});

it('passes with the minimum required fields', function () {
    expect(storeContactValidator(['email' => null, 'phone' => null, 'title' => null, 'notes' => null])->passes())
        ->toBeTrue();
});

it('rejects when first_name is missing', function () {
    $v = storeContactValidator(['first_name' => '']);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('first_name'))->toBeTrue();
});

it('rejects when last_name is missing', function () {
    $v = storeContactValidator(['last_name' => '']);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('last_name'))->toBeTrue();
});

it('rejects names longer than 100 characters', function () {
    $v = storeContactValidator(['first_name' => str_repeat('a', 101)]);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('first_name'))->toBeTrue();
});

it('rejects an invalid email format', function () {
    $v = storeContactValidator(['email' => 'not-an-email']);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('email'))->toBeTrue();
});

it('rejects a non-existent company_id', function () {
    $v = storeContactValidator(['company_id' => 999999]);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('company_id'))->toBeTrue();
});

it('passes when company_id exists', function () {
    $company = Company::factory()->create();

    expect(storeContactValidator(['company_id' => $company->id])->passes())->toBeTrue();
});

it('rejects when tag_ids is not an array', function () {
    $v = storeContactValidator(['tag_ids' => 'not-an-array']);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('tag_ids'))->toBeTrue();
});

it('rejects when an entry in tag_ids does not exist', function () {
    $v = storeContactValidator(['tag_ids' => [999999]]);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('tag_ids.0'))->toBeTrue();
});

it('passes with valid tag_ids', function () {
    $tags = Tag::factory()->count(2)->create();

    expect(storeContactValidator(['tag_ids' => $tags->pluck('id')->all()])->passes())
        ->toBeTrue();
});
