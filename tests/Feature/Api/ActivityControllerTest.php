<?php

use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('rejects index when unauthenticated', function () {
    $this->getJson('/api/activities')->assertStatus(401);
});

it('lists only the authenticated users activities', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    Activity::factory()->for($me)->count(2)->create();
    Activity::factory()->for($other)->count(3)->create();
    Sanctum::actingAs($me);

    $this->getJson('/api/activities')->assertOk()->assertJsonCount(2, 'data');
});

it('orders activities by occurred_at descending', function () {
    $me = User::factory()->create();
    $older = Activity::factory()->for($me)->create(['occurred_at' => now()->subDays(5)]);
    $newer = Activity::factory()->for($me)->create(['occurred_at' => now()]);
    Sanctum::actingAs($me);

    $response = $this->getJson('/api/activities');

    expect($response->json('data.0.id'))->toBe($newer->id)
        ->and($response->json('data.1.id'))->toBe($older->id);
});

it('searches activities across subject and body', function () {
    $me = User::factory()->create();
    Activity::factory()->for($me)->create(['subject' => 'Coffee chat', 'body' => 'Discussed Q4 roadmap']);
    Activity::factory()->for($me)->create(['subject' => 'Demo call', 'body' => 'Walked through pricing']);
    Sanctum::actingAs($me);

    expect($this->getJson('/api/activities?search=Coffee')->json('data'))->toHaveCount(1);
    expect($this->getJson('/api/activities?search=pricing')->json('data'))->toHaveCount(1);
    expect($this->getJson('/api/activities?search=zzz')->json('data'))->toHaveCount(0);
});

it('filters activities by type', function () {
    $me = User::factory()->create();
    Activity::factory()->for($me)->state(['type' => ActivityType::Call])->count(2)->create();
    Activity::factory()->for($me)->state(['type' => ActivityType::Note])->count(3)->create();
    Sanctum::actingAs($me);

    $this->getJson('/api/activities?type=Call')->assertJsonCount(2, 'data');
});

it('filters activities by contactId', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    Activity::factory()->for($me)->for($contact)->count(2)->create();
    Activity::factory()->for($me)->count(3)->create();
    Sanctum::actingAs($me);

    $this->getJson("/api/activities?contactId={$contact->id}")->assertJsonCount(2, 'data');
});

it('filters activities by dealId', function () {
    $me = User::factory()->create();
    $deal = Deal::factory()->for($me)->create();
    Activity::factory()->for($me)->state(['deal_id' => $deal->id])->count(2)->create();
    Activity::factory()->for($me)->count(3)->create();
    Sanctum::actingAs($me);

    $this->getJson("/api/activities?dealId={$deal->id}")->assertJsonCount(2, 'data');
});

it('respects the perPage query parameter', function () {
    $me = User::factory()->create();
    Activity::factory()->for($me)->count(5)->create();
    Sanctum::actingAs($me);

    $this->getJson('/api/activities?perPage=2')->assertJsonCount(2, 'data');
});

it('shows a single activity for the authenticated user', function () {
    $me = User::factory()->create();
    $activity = Activity::factory()->for($me)->create(['subject' => 'Show me']);
    Sanctum::actingAs($me);

    $response = $this->getJson("/api/activities/{$activity->id}");

    $response->assertOk()->assertJsonPath('data.subject', 'Show me');
});

it('returns 404 when showing a non-existent activity', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/activities/999999')->assertStatus(404);
});

it('forbids show when activity belongs to another user and the requester is not admin', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $activity = Activity::factory()->for($owner)->create();
    Sanctum::actingAs($other);

    $this->getJson("/api/activities/{$activity->id}")->assertStatus(403);
});

it('allows admin to show any activity', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $activity = Activity::factory()->for($owner)->create();
    Sanctum::actingAs($admin);

    $this->getJson("/api/activities/{$activity->id}")->assertOk();
});

it('creates an activity with all fields', function () {
    $me = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    $deal = Deal::factory()->for($me)->for($contact)->create();
    Sanctum::actingAs($me);

    $response = $this->postJson('/api/activities', [
        'type' => ActivityType::Meeting->value,
        'subject' => 'Kickoff',
        'body' => 'First meeting',
        'occurredAt' => '2026-03-01 10:00:00',
        'contactId' => $contact->id,
        'dealId' => $deal->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.subject', 'Kickoff')
        ->assertJsonPath('data.type', ActivityType::Meeting->value);

    $activity = Activity::where('subject', 'Kickoff')->first();
    expect($activity->user_id)->toBe($me->id)
        ->and($activity->contact_id)->toBe($contact->id)
        ->and($activity->deal_id)->toBe($deal->id);
});

it('creates an activity with only required fields', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/activities', [
        'type' => ActivityType::Note->value,
        'subject' => 'Solo',
        'occurredAt' => '2026-03-01 10:00:00',
    ])->assertCreated();
});

it('rejects store with missing required fields', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/activities', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['type', 'subject', 'occurredAt']);
});

it('rejects store with an invalid date for occurredAt', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/activities', [
        'type' => ActivityType::Note->value,
        'subject' => 'Bad Date',
        'occurredAt' => 'not-a-date',
    ])->assertStatus(422)->assertJsonValidationErrors('occurredAt');
});

it('rejects store with a non-existent contactId or dealId', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/activities', [
        'type' => ActivityType::Note->value,
        'subject' => 'Bad refs',
        'occurredAt' => '2026-03-01 10:00:00',
        'contactId' => 999999,
        'dealId' => 999999,
    ])->assertStatus(422)->assertJsonValidationErrors(['contactId', 'dealId']);
});

it('updates an activity owned by the authenticated user', function () {
    $me = User::factory()->create();
    $activity = Activity::factory()->for($me)->create(['subject' => 'Old']);
    Sanctum::actingAs($me);

    $response = $this->putJson("/api/activities/{$activity->id}", [
        'type' => ActivityType::Note->value,
        'subject' => 'New',
        'occurredAt' => '2026-03-01 10:00:00',
    ]);

    $response->assertOk()->assertJsonPath('data.subject', 'New');
    expect($activity->fresh()->subject)->toBe('New');
});

it('rejects update with missing required fields', function () {
    $me = User::factory()->create();
    $activity = Activity::factory()->for($me)->create();
    Sanctum::actingAs($me);

    $this->putJson("/api/activities/{$activity->id}", [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['type', 'subject', 'occurredAt']);
});

it('forbids update of an activity owned by another user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $activity = Activity::factory()->for($owner)->create();
    Sanctum::actingAs($other);

    $this->putJson("/api/activities/{$activity->id}", [
        'type' => ActivityType::Note->value,
        'subject' => 'Hack',
        'occurredAt' => '2026-03-01 10:00:00',
    ])->assertStatus(403);
});

it('returns 404 when updating a non-existent activity', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->putJson('/api/activities/999999', [
        'type' => ActivityType::Note->value,
        'subject' => 'Ghost',
        'occurredAt' => '2026-03-01 10:00:00',
    ])->assertStatus(404);
});

it('deletes an activity owned by the authenticated user', function () {
    $me = User::factory()->create();
    $activity = Activity::factory()->for($me)->create();
    Sanctum::actingAs($me);

    $this->deleteJson("/api/activities/{$activity->id}")->assertNoContent();
    expect(Activity::find($activity->id))->toBeNull();
});

it('forbids delete of an activity owned by another user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $activity = Activity::factory()->for($owner)->create();
    Sanctum::actingAs($other);

    $this->deleteJson("/api/activities/{$activity->id}")->assertStatus(403);
});

it('returns 404 when deleting a non-existent activity', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->deleteJson('/api/activities/999999')->assertStatus(404);
});
