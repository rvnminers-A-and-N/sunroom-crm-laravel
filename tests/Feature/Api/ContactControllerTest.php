<?php

use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Tag;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('rejects index when unauthenticated', function () {
    $this->getJson('/api/contacts')->assertStatus(401);
});

it('lists only the authenticated users contacts', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    Contact::factory()->for($me)->count(2)->create();
    Contact::factory()->for($other)->count(3)->create();
    Sanctum::actingAs($me);

    $response = $this->getJson('/api/contacts');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

it('orders index results by created_at descending', function () {
    $me = User::factory()->create();
    $older = Contact::factory()->for($me)->create(['created_at' => now()->subDays(2)]);
    $newer = Contact::factory()->for($me)->create(['created_at' => now()]);
    Sanctum::actingAs($me);

    $response = $this->getJson('/api/contacts');

    expect($response->json('data.0.id'))->toBe($newer->id)
        ->and($response->json('data.1.id'))->toBe($older->id);
});

it('searches contacts across first_name, last_name, and email', function () {
    $me = User::factory()->create();
    Contact::factory()->for($me)->create(['first_name' => 'Ada', 'last_name' => 'Lovelace', 'email' => 'ada@example.com']);
    Contact::factory()->for($me)->create(['first_name' => 'Grace', 'last_name' => 'Hopper', 'email' => 'grace@example.com']);
    Contact::factory()->for($me)->create(['first_name' => 'Bob', 'last_name' => 'Smith', 'email' => 'bob@something.test']);
    Sanctum::actingAs($me);

    expect($this->getJson('/api/contacts?search=ada')->json('data'))->toHaveCount(1);
    expect($this->getJson('/api/contacts?search=Hopper')->json('data'))->toHaveCount(1);
    expect($this->getJson('/api/contacts?search=something.test')->json('data'))->toHaveCount(1);
    expect($this->getJson('/api/contacts?search=zzz')->json('data'))->toHaveCount(0);
});

it('filters contacts by companyId', function () {
    $me = User::factory()->create();
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    Contact::factory()->for($me)->for($companyA)->count(2)->create();
    Contact::factory()->for($me)->for($companyB)->create();
    Sanctum::actingAs($me);

    $response = $this->getJson("/api/contacts?companyId={$companyA->id}");

    $response->assertOk()->assertJsonCount(2, 'data');
});

it('filters contacts by tagId', function () {
    $me = User::factory()->create();
    $tag = Tag::factory()->create();
    $tagged = Contact::factory()->for($me)->create();
    $tagged->tags()->attach($tag);
    Contact::factory()->for($me)->count(2)->create();
    Sanctum::actingAs($me);

    $response = $this->getJson("/api/contacts?tagId={$tag->id}");

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.id'))->toBe($tagged->id);
});

it('respects the perPage query parameter', function () {
    $me = User::factory()->create();
    Contact::factory()->for($me)->count(5)->create();
    Sanctum::actingAs($me);

    $response = $this->getJson('/api/contacts?perPage=2');

    $response->assertOk()->assertJsonCount(2, 'data');
});

it('returns the full contact payload from show', function () {
    $me = User::factory()->create();
    $company = Company::factory()->create(['name' => 'Acme', 'industry' => 'Software', 'city' => 'NYC', 'state' => 'NY']);
    $contact = Contact::factory()->for($me)->for($company)->create([
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'last_contacted_at' => '2026-01-15 10:00:00',
    ]);
    $contact->tags()->attach(Tag::factory()->count(2)->create());
    Deal::factory()->for($me)->for($contact)->create();
    Activity::factory()->for($me)->for($contact)->create();
    Sanctum::actingAs($me);

    $response = $this->getJson("/api/contacts/{$contact->id}");

    $response->assertOk()
        ->assertJsonStructure([
            'id', 'firstName', 'lastName', 'email', 'phone', 'title', 'notes',
            'lastContactedAt', 'createdAt', 'updatedAt',
            'company' => ['id', 'name', 'industry', 'city', 'state'],
            'tags', 'deals', 'activities',
        ])
        ->assertJsonPath('firstName', 'Ada')
        ->assertJsonPath('company.name', 'Acme');

    expect($response->json('tags'))->toHaveCount(2)
        ->and($response->json('deals'))->toHaveCount(1)
        ->and($response->json('activities'))->toHaveCount(1);
});

it('returns null company in show when contact has no company', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create(['company_id' => null]);
    Sanctum::actingAs($me);

    $response = $this->getJson("/api/contacts/{$contact->id}");

    $response->assertOk()->assertJsonPath('company', null);
});

it('returns 404 when showing a non-existent contact', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/contacts/999999')->assertStatus(404);
});

it('forbids show when contact belongs to another user and the requester is not admin', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $contact = Contact::factory()->for($owner)->create();
    Sanctum::actingAs($other);

    $this->getJson("/api/contacts/{$contact->id}")->assertStatus(403);
});

it('allows admin to show any contact', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $contact = Contact::factory()->for($owner)->create();
    Sanctum::actingAs($admin);

    $this->getJson("/api/contacts/{$contact->id}")->assertOk();
});

it('creates a contact with all optional fields and tag sync', function () {
    $me = User::factory()->create();
    $company = Company::factory()->create();
    $tags = Tag::factory()->count(2)->create();
    Sanctum::actingAs($me);

    $response = $this->postJson('/api/contacts', [
        'firstName' => 'Ada',
        'lastName' => 'Lovelace',
        'email' => 'ada@example.com',
        'phone' => '555-0100',
        'title' => 'Engineer',
        'notes' => 'Interesting',
        'companyId' => $company->id,
        'tagIds' => $tags->pluck('id')->all(),
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.firstName', 'Ada')
        ->assertJsonPath('data.companyId', $company->id);

    $contact = Contact::where('email', 'ada@example.com')->first();
    expect($contact)->not->toBeNull()
        ->and($contact->user_id)->toBe($me->id)
        ->and($contact->tags()->count())->toBe(2);
});

it('creates a contact with only required fields', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/contacts', [
        'firstName' => 'Solo',
        'lastName' => 'User',
    ]);

    $response->assertCreated();
    expect(Contact::where('first_name', 'Solo')->first()?->tags()->count())->toBe(0);
});

it('rejects store with missing required fields', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/contacts', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['firstName', 'lastName']);
});

it('rejects store with an invalid email', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/contacts', [
        'firstName' => 'Bad',
        'lastName' => 'Email',
        'email' => 'not-an-email',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors('email');
});

it('rejects store with a non-existent companyId', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/contacts', [
        'firstName' => 'Bad',
        'lastName' => 'Company',
        'companyId' => 999999,
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors('companyId');
});

it('rejects store with a non-existent tagId', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/contacts', [
        'firstName' => 'Bad',
        'lastName' => 'Tags',
        'tagIds' => [999999],
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors('tagIds.0');
});

it('updates a contact owned by the authenticated user', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create(['first_name' => 'Old']);
    Sanctum::actingAs($me);

    $response = $this->putJson("/api/contacts/{$contact->id}", [
        'firstName' => 'New',
        'lastName' => 'Name',
    ]);

    $response->assertOk()->assertJsonPath('data.firstName', 'New');
    expect($contact->fresh()->first_name)->toBe('New');
});

it('rejects update with missing required fields', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    Sanctum::actingAs($me);

    $response = $this->putJson("/api/contacts/{$contact->id}", []);

    $response->assertStatus(422)->assertJsonValidationErrors(['firstName', 'lastName']);
});

it('forbids update of a contact owned by another user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $contact = Contact::factory()->for($owner)->create();
    Sanctum::actingAs($other);

    $this->putJson("/api/contacts/{$contact->id}", [
        'firstName' => 'Hack',
        'lastName' => 'Attempt',
    ])->assertStatus(403);
});

it('returns 404 when updating a non-existent contact', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->putJson('/api/contacts/999999', [
        'firstName' => 'Ghost',
        'lastName' => 'User',
    ])->assertStatus(404);
});

it('deletes a contact owned by the authenticated user', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    Sanctum::actingAs($me);

    $this->deleteJson("/api/contacts/{$contact->id}")->assertNoContent();
    expect(Contact::find($contact->id))->toBeNull();
});

it('forbids delete of a contact owned by another user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $contact = Contact::factory()->for($owner)->create();
    Sanctum::actingAs($other);

    $this->deleteJson("/api/contacts/{$contact->id}")->assertStatus(403);
});

it('returns 404 when deleting a non-existent contact', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->deleteJson('/api/contacts/999999')->assertStatus(404);
});

it('syncs tags on a contact owned by the authenticated user', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    $existing = Tag::factory()->create();
    $contact->tags()->attach($existing);
    $newTags = Tag::factory()->count(2)->create();
    Sanctum::actingAs($me);

    $response = $this->postJson("/api/contacts/{$contact->id}/tags", [
        'tagIds' => $newTags->pluck('id')->all(),
    ]);

    $response->assertOk();
    $freshTagIds = $contact->fresh()->tags()->pluck('tags.id')->all();
    expect($freshTagIds)->toHaveCount(2)
        ->and($freshTagIds)->not->toContain($existing->id);
});

it('rejects syncTags with missing tagIds', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    Sanctum::actingAs($me);

    $response = $this->postJson("/api/contacts/{$contact->id}/tags", []);

    $response->assertStatus(422)->assertJsonValidationErrors('tagIds');
});

it('rejects syncTags with a non-existent tag', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    Sanctum::actingAs($me);

    $response = $this->postJson("/api/contacts/{$contact->id}/tags", [
        'tagIds' => [999999],
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors('tagIds.0');
});

it('forbids syncTags on a contact owned by another user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $contact = Contact::factory()->for($owner)->create();
    $tag = Tag::factory()->create();
    Sanctum::actingAs($other);

    $this->postJson("/api/contacts/{$contact->id}/tags", [
        'tagIds' => [$tag->id],
    ])->assertStatus(403);
});

it('returns 404 when syncing tags on a non-existent contact', function () {
    Sanctum::actingAs(User::factory()->create());
    $tag = Tag::factory()->create();

    $this->postJson('/api/contacts/999999/tags', [
        'tagIds' => [$tag->id],
    ])->assertStatus(404);
});
