<?php

use App\Http\Resources\ContactResource;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

it('serializes a contact with a loaded company and tags', function () {
    $company = Company::factory()->create(['name' => 'Acme Corp']);
    $contact = Contact::factory()->for($company)->create([
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'phone' => '555-0100',
        'title' => 'Engineer',
        'last_contacted_at' => '2026-01-15 10:00:00',
    ]);
    $tags = Tag::factory()->count(2)->create();
    $contact->tags()->attach($tags->pluck('id'));
    $contact->load(['company', 'tags']);

    $array = (new ContactResource($contact))->toArray(Request::create('/'));

    expect($array)
        ->toHaveKeys([
            'id', 'firstName', 'lastName', 'email', 'phone', 'title',
            'companyName', 'companyId', 'lastContactedAt', 'tags', 'createdAt',
        ])
        ->and($array['firstName'])->toBe('Ada')
        ->and($array['lastName'])->toBe('Lovelace')
        ->and($array['email'])->toBe('ada@example.com')
        ->and($array['phone'])->toBe('555-0100')
        ->and($array['title'])->toBe('Engineer')
        ->and($array['companyName'])->toBe('Acme Corp')
        ->and($array['companyId'])->toBe($company->id)
        ->and($array['lastContactedAt'])->toBeString()
        ->and($array['tags'])->toHaveCount(2);
});

it('serializes a contact without a company or tags loaded', function () {
    $contact = Contact::factory()->create([
        'company_id' => null,
        'last_contacted_at' => null,
    ]);

    $request = Request::create('/');
    $payload = (new ContactResource($contact))->response($request)->getData(true);
    $data = $payload['data'];

    expect($data['companyName'])->toBeNull()
        ->and($data['companyId'])->toBeNull()
        ->and($data['lastContactedAt'])->toBeNull()
        ->and($data)->not->toHaveKey('tags');
});
