<?php

use App\Enums\DealStage;
use App\Http\Resources\DealResource;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

it('serializes a deal with all populated relations', function () {
    $contact = Contact::factory()->create([
        'first_name' => 'Grace',
        'last_name' => 'Hopper',
    ]);
    $company = Company::factory()->create(['name' => 'COBOL Inc']);
    $deal = Deal::factory()
        ->for($contact)
        ->for($company)
        ->create([
            'title' => 'Big Deal',
            'value' => 12500.50,
            'stage' => DealStage::Negotiation,
            'expected_close_date' => '2026-12-31',
            'closed_at' => null,
        ]);
    $deal->load(['contact', 'company']);

    $array = (new DealResource($deal))->toArray(Request::create('/'));

    expect($array)
        ->toHaveKeys([
            'id', 'title', 'value', 'stage', 'contactId', 'contactName',
            'companyId', 'companyName', 'expectedCloseDate', 'closedAt', 'createdAt',
        ])
        ->and($array['title'])->toBe('Big Deal')
        ->and($array['value'])->toBe(12500.50)
        ->and($array['stage'])->toBe('Negotiation')
        ->and($array['contactName'])->toBe('Grace Hopper')
        ->and($array['companyName'])->toBe('COBOL Inc')
        ->and($array['expectedCloseDate'])->toBeString()
        ->and($array['closedAt'])->toBeNull();
});

it('serializes a deal without a contact or company', function () {
    $deal = Deal::factory()->create([
        'company_id' => null,
    ]);
    $deal->setRelation('contact', null);
    $deal->setRelation('company', null);

    $array = (new DealResource($deal))->toArray(Request::create('/'));

    expect($array['contactName'])->toBe('')
        ->and($array['companyName'])->toBeNull();
});

it('casts the value field to a float', function () {
    $deal = Deal::factory()->create(['value' => 100]);

    $array = (new DealResource($deal))->toArray(Request::create('/'));

    expect($array['value'])->toBeFloat();
});
