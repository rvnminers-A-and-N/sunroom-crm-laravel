<?php

use App\Enums\ActivityType;
use App\Http\Requests\StoreActivityRequest;
use App\Models\Contact;
use App\Models\Deal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

function storeActivityValidator(array $overrides = []): Illuminate\Validation\Validator
{
    $defaults = [
        'type' => ActivityType::Note->value,
        'subject' => 'Met for coffee',
        'body' => 'Discussed roadmap',
        'occurred_at' => '2026-02-01 10:00:00',
    ];

    return Validator::make(array_merge($defaults, $overrides), (new StoreActivityRequest)->rules());
}

it('authorizes the request unconditionally', function () {
    expect((new StoreActivityRequest)->authorize())->toBeTrue();
});

it('passes with all required fields', function () {
    expect(storeActivityValidator()->passes())->toBeTrue();
});

it('rejects an invalid type value', function () {
    $v = storeActivityValidator(['type' => 'NotAType']);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('type'))->toBeTrue();
});

it('accepts every defined ActivityType value', function () {
    foreach (ActivityType::cases() as $type) {
        expect(storeActivityValidator(['type' => $type->value])->passes())->toBeTrue();
    }
});

it('rejects when subject is missing', function () {
    $v = storeActivityValidator(['subject' => '']);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('subject'))->toBeTrue();
});

it('rejects subjects longer than 255 characters', function () {
    $v = storeActivityValidator(['subject' => str_repeat('a', 256)]);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('subject'))->toBeTrue();
});

it('rejects when occurred_at is missing', function () {
    $v = storeActivityValidator(['occurred_at' => '']);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('occurred_at'))->toBeTrue();
});

it('rejects an invalid occurred_at format', function () {
    $v = storeActivityValidator(['occurred_at' => 'not-a-date']);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('occurred_at'))->toBeTrue();
});

it('rejects a non-existent contact_id', function () {
    $v = storeActivityValidator(['contact_id' => 999999]);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('contact_id'))->toBeTrue();
});

it('passes when contact_id exists', function () {
    $contact = Contact::factory()->create();

    expect(storeActivityValidator(['contact_id' => $contact->id])->passes())->toBeTrue();
});

it('rejects a non-existent deal_id', function () {
    $v = storeActivityValidator(['deal_id' => 999999]);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('deal_id'))->toBeTrue();
});

it('passes when deal_id exists', function () {
    $deal = Deal::factory()->create();

    expect(storeActivityValidator(['deal_id' => $deal->id])->passes())->toBeTrue();
});
