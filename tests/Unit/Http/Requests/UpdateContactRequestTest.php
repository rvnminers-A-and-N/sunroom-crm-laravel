<?php

use App\Http\Requests\UpdateContactRequest;
use App\Models\Company;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

function updateContactValidator(array $overrides = []): Illuminate\Validation\Validator
{
    $defaults = [
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
    ];

    return Validator::make(array_merge($defaults, $overrides), (new UpdateContactRequest)->rules());
}

it('authorizes the request unconditionally', function () {
    expect((new UpdateContactRequest)->authorize())->toBeTrue();
});

it('passes with the minimum required fields', function () {
    expect(updateContactValidator()->passes())->toBeTrue();
});

it('rejects when first_name is missing', function () {
    $v = updateContactValidator(['first_name' => '']);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('first_name'))->toBeTrue();
});

it('rejects when last_name is missing', function () {
    $v = updateContactValidator(['last_name' => '']);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('last_name'))->toBeTrue();
});

it('rejects an invalid email format', function () {
    $v = updateContactValidator(['email' => 'not-an-email']);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('email'))->toBeTrue();
});

it('rejects a non-existent company_id', function () {
    $v = updateContactValidator(['company_id' => 999999]);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('company_id'))->toBeTrue();
});

it('passes when company_id exists', function () {
    $company = Company::factory()->create();

    expect(updateContactValidator(['company_id' => $company->id])->passes())->toBeTrue();
});

it('rejects when an entry in tag_ids does not exist', function () {
    $v = updateContactValidator(['tag_ids' => [999999]]);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('tag_ids.0'))->toBeTrue();
});

it('passes with valid tag_ids', function () {
    $tags = Tag::factory()->count(2)->create();

    expect(updateContactValidator(['tag_ids' => $tags->pluck('id')->all()])->passes())
        ->toBeTrue();
});
