<?php

use App\Enums\DealStage;
use App\Http\Requests\StoreDealRequest;
use App\Models\Company;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

function storeDealValidator(array $overrides = []): Illuminate\Validation\Validator
{
    $contact = $overrides['contact'] ?? Contact::factory()->create();
    unset($overrides['contact']);

    $defaults = [
        'title' => 'Big Deal',
        'value' => 5000,
        'stage' => DealStage::Lead->value,
        'contact_id' => $contact->id,
    ];

    return Validator::make(array_merge($defaults, $overrides), (new StoreDealRequest)->rules());
}

it('authorizes the request unconditionally', function () {
    expect((new StoreDealRequest)->authorize())->toBeTrue();
});

it('passes with all required fields', function () {
    expect(storeDealValidator()->passes())->toBeTrue();
});

it('rejects when title is missing', function () {
    $v = storeDealValidator(['title' => '']);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('title'))->toBeTrue();
});

it('rejects when value is not numeric', function () {
    $v = storeDealValidator(['value' => 'free']);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('value'))->toBeTrue();
});

it('rejects when value is negative', function () {
    $v = storeDealValidator(['value' => -1]);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('value'))->toBeTrue();
});

it('rejects an invalid stage value', function () {
    $v = storeDealValidator(['stage' => 'NotAStage']);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('stage'))->toBeTrue();
});

it('accepts every defined DealStage value', function () {
    foreach (DealStage::cases() as $stage) {
        expect(storeDealValidator(['stage' => $stage->value])->passes())->toBeTrue();
    }
});

it('rejects a non-existent contact_id', function () {
    $v = storeDealValidator(['contact_id' => 999999]);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('contact_id'))->toBeTrue();
});

it('rejects a non-existent company_id', function () {
    $v = storeDealValidator(['company_id' => 999999]);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('company_id'))->toBeTrue();
});

it('passes when company_id exists', function () {
    $company = Company::factory()->create();

    expect(storeDealValidator(['company_id' => $company->id])->passes())->toBeTrue();
});

it('rejects an invalid expected_close_date', function () {
    $v = storeDealValidator(['expected_close_date' => 'not-a-date']);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('expected_close_date'))->toBeTrue();
});
