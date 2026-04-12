<?php

use App\Http\Requests\StoreCompanyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

function storeCompanyValidator(array $overrides = []): Illuminate\Validation\Validator
{
    $defaults = ['name' => 'Acme'];

    return Validator::make(array_merge($defaults, $overrides), (new StoreCompanyRequest)->rules());
}

it('authorizes the request unconditionally', function () {
    expect((new StoreCompanyRequest)->authorize())->toBeTrue();
});

it('passes with only the required name', function () {
    expect(storeCompanyValidator()->passes())->toBeTrue();
});

it('rejects when name is missing', function () {
    $v = storeCompanyValidator(['name' => '']);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('name'))->toBeTrue();
});

it('rejects names longer than 255 characters', function () {
    $v = storeCompanyValidator(['name' => str_repeat('a', 256)]);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('name'))->toBeTrue();
});

it('rejects an invalid website url', function () {
    $v = storeCompanyValidator(['website' => 'not-a-url']);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('website'))->toBeTrue();
});

it('passes with a valid website url', function () {
    expect(storeCompanyValidator(['website' => 'https://acme.test'])->passes())->toBeTrue();
});

it('rejects city longer than 100 characters', function () {
    $v = storeCompanyValidator(['city' => str_repeat('a', 101)]);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('city'))->toBeTrue();
});

it('rejects state longer than 50 characters', function () {
    $v = storeCompanyValidator(['state' => str_repeat('a', 51)]);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('state'))->toBeTrue();
});

it('rejects zip longer than 20 characters', function () {
    $v = storeCompanyValidator(['zip' => str_repeat('1', 21)]);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('zip'))->toBeTrue();
});

it('passes when all optional fields are provided', function () {
    expect(storeCompanyValidator([
        'industry' => 'Software',
        'website' => 'https://acme.test',
        'phone' => '555-0100',
        'address' => '1 Loop',
        'city' => 'Springfield',
        'state' => 'IL',
        'zip' => '62701',
        'notes' => 'Top customer',
    ])->passes())->toBeTrue();
});
