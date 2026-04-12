<?php

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('rejects index when unauthenticated', function () {
    $this->getJson('/api/companies')->assertStatus(401);
});

it('lists only the authenticated users companies with counts', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $mine = Company::factory()->for($me)->create();
    Contact::factory()->for($me)->for($mine)->count(3)->create();
    Deal::factory()->for($me)->state(['company_id' => $mine->id])->count(2)->create();
    Company::factory()->for($other)->count(2)->create();
    Sanctum::actingAs($me);

    $response = $this->getJson('/api/companies');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.contactCount'))->toBe(3)
        ->and($response->json('data.0.dealCount'))->toBe(2);
});

it('orders companies alphabetically by name', function () {
    $me = User::factory()->create();
    Company::factory()->for($me)->create(['name' => 'Charlie Co']);
    Company::factory()->for($me)->create(['name' => 'Alpha Inc']);
    Company::factory()->for($me)->create(['name' => 'Bravo Ltd']);
    Sanctum::actingAs($me);

    $names = collect($this->getJson('/api/companies')->json('data'))->pluck('name')->all();

    expect($names)->toBe(['Alpha Inc', 'Bravo Ltd', 'Charlie Co']);
});

it('searches companies across name, industry, and city', function () {
    $me = User::factory()->create();
    Company::factory()->for($me)->create(['name' => 'Acme Corp', 'industry' => 'Software', 'city' => 'NYC']);
    Company::factory()->for($me)->create(['name' => 'Globex', 'industry' => 'Manufacturing', 'city' => 'Boston']);
    Company::factory()->for($me)->create(['name' => 'Initech', 'industry' => 'IT', 'city' => 'Austin']);
    Sanctum::actingAs($me);

    expect($this->getJson('/api/companies?search=Acme')->json('data'))->toHaveCount(1);
    expect($this->getJson('/api/companies?search=Manufacturing')->json('data'))->toHaveCount(1);
    expect($this->getJson('/api/companies?search=Austin')->json('data'))->toHaveCount(1);
    expect($this->getJson('/api/companies?search=zzz')->json('data'))->toHaveCount(0);
});

it('respects the perPage query parameter', function () {
    $me = User::factory()->create();
    Company::factory()->for($me)->count(5)->create();
    Sanctum::actingAs($me);

    $response = $this->getJson('/api/companies?perPage=2');

    $response->assertOk()->assertJsonCount(2, 'data');
});

it('returns the full company payload from show', function () {
    $me = User::factory()->create();
    $company = Company::factory()->for($me)->create([
        'name' => 'Acme',
        'industry' => 'Software',
        'website' => 'https://acme.test',
        'city' => 'NYC',
        'state' => 'NY',
        'zip' => '10001',
        'notes' => 'Top customer',
    ]);
    Contact::factory()->for($me)->for($company)->count(2)->create();
    Deal::factory()->for($me)->state(['company_id' => $company->id])->create();
    Sanctum::actingAs($me);

    $response = $this->getJson("/api/companies/{$company->id}");

    $response->assertOk()
        ->assertJsonStructure([
            'id', 'name', 'industry', 'website', 'phone', 'address', 'city', 'state', 'zip',
            'notes', 'createdAt', 'updatedAt', 'contacts', 'deals',
        ])
        ->assertJsonPath('name', 'Acme')
        ->assertJsonPath('industry', 'Software');

    expect($response->json('contacts'))->toHaveCount(2)
        ->and($response->json('deals'))->toHaveCount(1);
});

it('returns 404 when showing a non-existent company', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/companies/999999')->assertStatus(404);
});

it('forbids show when company belongs to another user and the requester is not admin', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $company = Company::factory()->for($owner)->create();
    Sanctum::actingAs($other);

    $this->getJson("/api/companies/{$company->id}")->assertStatus(403);
});

it('allows admin to show any company', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $company = Company::factory()->for($owner)->create();
    Sanctum::actingAs($admin);

    $this->getJson("/api/companies/{$company->id}")->assertOk();
});

it('creates a company with all fields', function () {
    $me = User::factory()->create();
    Sanctum::actingAs($me);

    $response = $this->postJson('/api/companies', [
        'name' => 'New Co',
        'industry' => 'Software',
        'website' => 'https://new.test',
        'phone' => '555-0100',
        'address' => '1 Loop',
        'city' => 'Springfield',
        'state' => 'IL',
        'zip' => '62701',
        'notes' => 'fresh',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'New Co');

    $company = Company::where('name', 'New Co')->first();
    expect($company)->not->toBeNull()
        ->and($company->user_id)->toBe($me->id);
});

it('creates a company with only the required name', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/companies', ['name' => 'Solo']);

    $response->assertCreated();
});

it('rejects store with missing name', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/companies', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

it('rejects store with an invalid website url', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/companies', [
        'name' => 'Bad URL',
        'website' => 'not-a-url',
    ])->assertStatus(422)->assertJsonValidationErrors('website');
});

it('updates a company owned by the authenticated user', function () {
    $me = User::factory()->create();
    $company = Company::factory()->for($me)->create(['name' => 'Old']);
    Sanctum::actingAs($me);

    $response = $this->putJson("/api/companies/{$company->id}", ['name' => 'New']);

    $response->assertOk()->assertJsonPath('data.name', 'New');
    expect($company->fresh()->name)->toBe('New');
});

it('rejects update with missing name', function () {
    $me = User::factory()->create();
    $company = Company::factory()->for($me)->create();
    Sanctum::actingAs($me);

    $this->putJson("/api/companies/{$company->id}", [])
        ->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

it('forbids update of a company owned by another user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $company = Company::factory()->for($owner)->create();
    Sanctum::actingAs($other);

    $this->putJson("/api/companies/{$company->id}", ['name' => 'Hack'])->assertStatus(403);
});

it('returns 404 when updating a non-existent company', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->putJson('/api/companies/999999', ['name' => 'Ghost'])->assertStatus(404);
});

it('deletes a company owned by the authenticated user', function () {
    $me = User::factory()->create();
    $company = Company::factory()->for($me)->create();
    Sanctum::actingAs($me);

    $this->deleteJson("/api/companies/{$company->id}")->assertNoContent();
    expect(Company::find($company->id))->toBeNull();
});

it('forbids delete of a company owned by another user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $company = Company::factory()->for($owner)->create();
    Sanctum::actingAs($other);

    $this->deleteJson("/api/companies/{$company->id}")->assertStatus(403);
});

it('returns 404 when deleting a non-existent company', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->deleteJson('/api/companies/999999')->assertStatus(404);
});
