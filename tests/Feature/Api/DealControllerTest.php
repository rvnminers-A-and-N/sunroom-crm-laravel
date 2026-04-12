<?php

use App\Enums\DealStage;
use App\Models\Activity;
use App\Models\AiInsight;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

it('rejects index when unauthenticated', function () {
    $this->getJson('/api/deals')->assertStatus(401);
});

it('lists only the authenticated users deals', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    Deal::factory()->for($me)->count(2)->create();
    Deal::factory()->for($other)->count(3)->create();
    Sanctum::actingAs($me);

    $this->getJson('/api/deals')->assertOk()->assertJsonCount(2, 'data');
});

it('orders deals by created_at descending', function () {
    $me = User::factory()->create();
    $older = Deal::factory()->for($me)->create(['created_at' => now()->subDays(2)]);
    $newer = Deal::factory()->for($me)->create(['created_at' => now()]);
    Sanctum::actingAs($me);

    $response = $this->getJson('/api/deals');

    expect($response->json('data.0.id'))->toBe($newer->id)
        ->and($response->json('data.1.id'))->toBe($older->id);
});

it('searches deals by title', function () {
    $me = User::factory()->create();
    Deal::factory()->for($me)->create(['title' => 'Acme Software License']);
    Deal::factory()->for($me)->create(['title' => 'Globex Hardware Order']);
    Sanctum::actingAs($me);

    expect($this->getJson('/api/deals?search=Software')->json('data'))->toHaveCount(1);
    expect($this->getJson('/api/deals?search=Globex')->json('data'))->toHaveCount(1);
    expect($this->getJson('/api/deals?search=zzz')->json('data'))->toHaveCount(0);
});

it('filters deals by stage', function () {
    $me = User::factory()->create();
    Deal::factory()->for($me)->state(['stage' => DealStage::Won])->count(2)->create();
    Deal::factory()->for($me)->state(['stage' => DealStage::Lead])->count(3)->create();
    Sanctum::actingAs($me);

    $response = $this->getJson('/api/deals?stage=Won');

    $response->assertOk()->assertJsonCount(2, 'data');
});

it('filters deals by contactId', function () {
    $me = User::factory()->create();
    $contactA = Contact::factory()->for($me)->create();
    $contactB = Contact::factory()->for($me)->create();
    Deal::factory()->for($me)->for($contactA)->count(2)->create();
    Deal::factory()->for($me)->for($contactB)->create();
    Sanctum::actingAs($me);

    $this->getJson("/api/deals?contactId={$contactA->id}")->assertJsonCount(2, 'data');
});

it('filters deals by companyId', function () {
    $me = User::factory()->create();
    $company = Company::factory()->for($me)->create();
    Deal::factory()->for($me)->state(['company_id' => $company->id])->count(2)->create();
    Deal::factory()->for($me)->create();
    Sanctum::actingAs($me);

    $this->getJson("/api/deals?companyId={$company->id}")->assertJsonCount(2, 'data');
});

it('respects the perPage query parameter', function () {
    $me = User::factory()->create();
    Deal::factory()->for($me)->count(5)->create();
    Sanctum::actingAs($me);

    $this->getJson('/api/deals?perPage=2')->assertJsonCount(2, 'data');
});

it('returns the full deal payload from show', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create(['first_name' => 'Ada', 'last_name' => 'Lovelace']);
    $company = Company::factory()->for($me)->create(['name' => 'Acme']);
    $deal = Deal::factory()->for($me)->for($contact)->state([
        'company_id' => $company->id,
        'title' => 'Big Deal',
        'value' => 12345.67,
        'stage' => DealStage::Proposal,
    ])->create();
    Activity::factory()->for($me)->for($contact)->state(['deal_id' => $deal->id])->create();
    AiInsight::factory()->state(['deal_id' => $deal->id])->create();
    Sanctum::actingAs($me);

    $response = $this->getJson("/api/deals/{$deal->id}");

    $response->assertOk()
        ->assertJsonStructure([
            'id', 'title', 'value', 'stage', 'contactId', 'contactName', 'companyId',
            'companyName', 'expectedCloseDate', 'closedAt', 'notes', 'createdAt', 'updatedAt',
            'activities', 'insights',
        ])
        ->assertJsonPath('title', 'Big Deal')
        ->assertJsonPath('value', 12345.67)
        ->assertJsonPath('stage', DealStage::Proposal->value)
        ->assertJsonPath('contactName', 'Ada Lovelace')
        ->assertJsonPath('companyName', 'Acme');

    expect($response->json('activities'))->toHaveCount(1)
        ->and($response->json('insights'))->toHaveCount(1);
});

it('returns empty contactName and null companyName when relations are missing in show', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    $deal = Deal::factory()->for($me)->for($contact)->state(['company_id' => null])->create();

    // Drop the RESTRICT foreign key so we can simulate an orphaned deal,
    // then restore it. This is the only way to exercise the missing-contact
    // branch of the show() payload without superuser privileges.
    DB::statement('ALTER TABLE deals DROP CONSTRAINT deals_contact_id_foreign');
    $contact->delete();
    // RefreshDatabase rolls back the whole transaction at the end of the test,
    // so we don't need to recreate the constraint.

    Sanctum::actingAs($me);

    $response = $this->getJson("/api/deals/{$deal->id}");

    $response->assertOk()
        ->assertJsonPath('contactName', '')
        ->assertJsonPath('companyName', null);
});

it('returns 404 when showing a non-existent deal', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/deals/999999')->assertStatus(404);
});

it('forbids show when deal belongs to another user and the requester is not admin', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $deal = Deal::factory()->for($owner)->create();
    Sanctum::actingAs($other);

    $this->getJson("/api/deals/{$deal->id}")->assertStatus(403);
});

it('allows admin to show any deal', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $deal = Deal::factory()->for($owner)->create();
    Sanctum::actingAs($admin);

    $this->getJson("/api/deals/{$deal->id}")->assertOk();
});

it('creates a deal with all fields and the default Lead stage', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    $company = Company::factory()->for($me)->create();
    Sanctum::actingAs($me);

    $response = $this->postJson('/api/deals', [
        'title' => 'New Deal',
        'value' => 1000,
        'contactId' => $contact->id,
        'companyId' => $company->id,
        'expectedCloseDate' => '2026-12-31',
        'notes' => 'fresh',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'New Deal')
        ->assertJsonPath('data.stage', DealStage::Lead->value);

    $deal = Deal::where('title', 'New Deal')->first();
    expect($deal->stage)->toBe(DealStage::Lead)
        ->and($deal->closed_at)->toBeNull();
});

it('sets closed_at when creating a deal in the Won stage', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    Sanctum::actingAs($me);

    $response = $this->postJson('/api/deals', [
        'title' => 'Quick Win',
        'value' => 2000,
        'stage' => DealStage::Won->value,
        'contactId' => $contact->id,
    ]);

    $response->assertCreated();
    expect(Deal::where('title', 'Quick Win')->first()->closed_at)->not->toBeNull();
});

it('sets closed_at when creating a deal in the Lost stage', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    Sanctum::actingAs($me);

    $this->postJson('/api/deals', [
        'title' => 'Quick Loss',
        'value' => 100,
        'stage' => DealStage::Lost->value,
        'contactId' => $contact->id,
    ])->assertCreated();

    expect(Deal::where('title', 'Quick Loss')->first()->closed_at)->not->toBeNull();
});

it('falls back to Lead when an invalid stage string is provided on store', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    Sanctum::actingAs($me);

    $this->postJson('/api/deals', [
        'title' => 'Bad Stage',
        'value' => 100,
        'stage' => 'NotAStage',
        'contactId' => $contact->id,
    ])->assertCreated();

    expect(Deal::where('title', 'Bad Stage')->first()->stage)->toBe(DealStage::Lead);
});

it('rejects store with missing required fields', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/deals', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'value', 'contactId']);
});

it('rejects store with a non-numeric value', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    Sanctum::actingAs($me);

    $this->postJson('/api/deals', [
        'title' => 'Bad',
        'value' => 'free',
        'contactId' => $contact->id,
    ])->assertStatus(422)->assertJsonValidationErrors('value');
});

it('rejects store with a non-existent contactId', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/deals', [
        'title' => 'Bad Contact',
        'value' => 100,
        'contactId' => 999999,
    ])->assertStatus(422)->assertJsonValidationErrors('contactId');
});

it('updates a deal owned by the authenticated user', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    $deal = Deal::factory()->for($me)->for($contact)->state(['stage' => DealStage::Lead])->create();
    Sanctum::actingAs($me);

    $response = $this->putJson("/api/deals/{$deal->id}", [
        'title' => 'Renamed',
        'value' => 9999,
        'contactId' => $contact->id,
    ]);

    $response->assertOk()->assertJsonPath('data.title', 'Renamed');
    expect($deal->fresh()->title)->toBe('Renamed');
});

it('sets closed_at when updating a deal to Won', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    $deal = Deal::factory()->for($me)->for($contact)->state(['stage' => DealStage::Lead, 'closed_at' => null])->create();
    Sanctum::actingAs($me);

    $this->putJson("/api/deals/{$deal->id}", [
        'title' => $deal->title,
        'value' => $deal->value,
        'stage' => DealStage::Won->value,
        'contactId' => $contact->id,
    ])->assertOk();

    expect($deal->fresh()->closed_at)->not->toBeNull();
});

it('clears closed_at when moving a deal back from Won to Negotiation', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    $deal = Deal::factory()->for($me)->for($contact)->state([
        'stage' => DealStage::Won,
        'closed_at' => now(),
    ])->create();
    Sanctum::actingAs($me);

    $this->putJson("/api/deals/{$deal->id}", [
        'title' => $deal->title,
        'value' => $deal->value,
        'stage' => DealStage::Negotiation->value,
        'contactId' => $contact->id,
    ])->assertOk();

    expect($deal->fresh()->closed_at)->toBeNull();
});

it('does not change closed_at when updating a Won deal to Won', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    $when = now()->subDays(5)->startOfSecond();
    $deal = Deal::factory()->for($me)->for($contact)->state([
        'stage' => DealStage::Won,
        'closed_at' => $when,
    ])->create();
    Sanctum::actingAs($me);

    $this->putJson("/api/deals/{$deal->id}", [
        'title' => $deal->title,
        'value' => $deal->value,
        'stage' => DealStage::Won->value,
        'contactId' => $contact->id,
    ])->assertOk();

    expect($deal->fresh()->closed_at?->toIso8601String())->toBe($when->toIso8601String());
});

it('ignores an invalid stage string on update and keeps the existing stage', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    $deal = Deal::factory()->for($me)->for($contact)->state(['stage' => DealStage::Qualified])->create();
    Sanctum::actingAs($me);

    $this->putJson("/api/deals/{$deal->id}", [
        'title' => $deal->title,
        'value' => $deal->value,
        'stage' => 'NotAStage',
        'contactId' => $contact->id,
    ])->assertOk();

    expect($deal->fresh()->stage)->toBe(DealStage::Qualified);
});

it('rejects update with missing required fields', function () {
    $me = User::factory()->create();
    $deal = Deal::factory()->for($me)->create();
    Sanctum::actingAs($me);

    $this->putJson("/api/deals/{$deal->id}", [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'value', 'contactId']);
});

it('forbids update of a deal owned by another user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $deal = Deal::factory()->for($owner)->create();
    $contact = Contact::factory()->for($other)->create();
    Sanctum::actingAs($other);

    $this->putJson("/api/deals/{$deal->id}", [
        'title' => 'Hack',
        'value' => 1,
        'contactId' => $contact->id,
    ])->assertStatus(403);
});

it('returns 404 when updating a non-existent deal', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    Sanctum::actingAs($me);

    $this->putJson('/api/deals/999999', [
        'title' => 'Ghost',
        'value' => 1,
        'contactId' => $contact->id,
    ])->assertStatus(404);
});

it('deletes a deal owned by the authenticated user', function () {
    $me = User::factory()->create();
    $deal = Deal::factory()->for($me)->create();
    Sanctum::actingAs($me);

    $this->deleteJson("/api/deals/{$deal->id}")->assertNoContent();
    expect(Deal::find($deal->id))->toBeNull();
});

it('forbids delete of a deal owned by another user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $deal = Deal::factory()->for($owner)->create();
    Sanctum::actingAs($other);

    $this->deleteJson("/api/deals/{$deal->id}")->assertStatus(403);
});

it('returns 404 when deleting a non-existent deal', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->deleteJson('/api/deals/999999')->assertStatus(404);
});

it('returns the pipeline grouped by stage with counts and totals', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    Deal::factory()->for($me)->state(['stage' => DealStage::Lead, 'value' => 100])->create();
    Deal::factory()->for($me)->state(['stage' => DealStage::Lead, 'value' => 200])->create();
    Deal::factory()->for($me)->state(['stage' => DealStage::Won, 'value' => 5000])->create();
    Deal::factory()->for($other)->state(['stage' => DealStage::Lead, 'value' => 999999])->create();
    Sanctum::actingAs($me);

    $response = $this->getJson('/api/deals/pipeline');

    $response->assertOk();
    $stages = collect($response->json('stages'))->keyBy('stage');

    expect($stages->keys()->all())->toBe(array_column(DealStage::cases(), 'value'))
        ->and($stages['Lead']['count'])->toBe(2)
        ->and((float) $stages['Lead']['totalValue'])->toBe(300.0)
        ->and($stages['Won']['count'])->toBe(1)
        ->and((float) $stages['Won']['totalValue'])->toBe(5000.0)
        ->and($stages['Qualified']['count'])->toBe(0)
        ->and((float) $stages['Qualified']['totalValue'])->toBe(0.0);
});

it('rejects pipeline when unauthenticated', function () {
    $this->getJson('/api/deals/pipeline')->assertStatus(401);
});
