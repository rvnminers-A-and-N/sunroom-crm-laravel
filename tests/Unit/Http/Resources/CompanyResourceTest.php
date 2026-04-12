<?php

use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MissingValue;

uses(RefreshDatabase::class);

it('serializes a company with all base fields populated', function () {
    $company = Company::factory()->create([
        'name' => 'Acme Corp',
        'industry' => 'Software',
        'website' => 'https://acme.test',
        'phone' => '555-0100',
        'address' => '1 Loop',
        'city' => 'Springfield',
        'state' => 'IL',
        'zip' => '62701',
        'notes' => 'Top customer',
    ]);

    $array = (new CompanyResource($company))->toArray(Request::create('/'));

    expect($array)
        ->toHaveKeys([
            'id', 'name', 'industry', 'website', 'phone',
            'address', 'city', 'state', 'zip', 'notes',
            'contactCount', 'dealCount', 'createdAt', 'updatedAt',
        ])
        ->and($array['name'])->toBe('Acme Corp')
        ->and($array['industry'])->toBe('Software')
        ->and($array['website'])->toBe('https://acme.test')
        ->and($array['createdAt'])->toBeString()
        ->and($array['updatedAt'])->toBeString();
});

it('omits contactCount and dealCount when relations are not counted', function () {
    $company = Company::factory()->create();

    $array = (new CompanyResource($company))->toArray(Request::create('/'));

    expect($array['contactCount'])->toBeInstanceOf(MissingValue::class)
        ->and($array['dealCount'])->toBeInstanceOf(MissingValue::class);
});

it('exposes contactCount and dealCount when relations are counted', function () {
    $company = Company::factory()->create();
    Contact::factory()->for($company)->count(3)->create();
    Deal::factory()->for($company)->count(2)->create();

    $loaded = Company::query()->withCount(['contacts', 'deals'])->find($company->id);

    $array = (new CompanyResource($loaded))->toArray(Request::create('/'));

    expect($array['contactCount'])->toBe(3)
        ->and($array['dealCount'])->toBe(2);
});
