<?php

use App\Enums\ActivityType;
use App\Livewire\Activities\ActivityList;
use App\Models\Activity;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Livewire\Livewire;

it('redirects to login when not authenticated', function () {
    $this->get(route('activities.index'))->assertRedirect('/login');
});

it('renders only activities for the authenticated user', function () {
    $me = actingAsUser();
    $other = User::factory()->create();
    Activity::factory()->for($me)->create(['subject' => 'Mine']);
    Activity::factory()->for($other)->create(['subject' => 'Theirs']);

    Livewire::test(ActivityList::class)
        ->assertStatus(200)
        ->assertSee('Mine')
        ->assertDontSee('Theirs');
});

it('searches activities by subject and body', function () {
    $me = actingAsUser();
    Activity::factory()->for($me)->create(['subject' => 'Discovery call', 'body' => 'demo notes']);
    Activity::factory()->for($me)->create(['subject' => 'Follow-up', 'body' => 'pricing question']);

    Livewire::test(ActivityList::class)
        ->set('search', 'Discovery')
        ->assertSee('Discovery call')
        ->assertDontSee('Follow-up');

    Livewire::test(ActivityList::class)
        ->set('search', 'pricing')
        ->assertSee('Follow-up')
        ->assertDontSee('Discovery');
});

it('filters by type via setTypeFilter and resets pagination', function () {
    $me = actingAsUser();
    Activity::factory()->for($me)->create(['type' => ActivityType::Call, 'subject' => 'A call']);
    Activity::factory()->for($me)->create(['type' => ActivityType::Note, 'subject' => 'A note']);

    Livewire::test(ActivityList::class)
        ->call('setTypeFilter', 'Call')
        ->assertSet('typeFilter', 'Call')
        ->assertSee('A call')
        ->assertDontSee('A note');
});

it('filters by type when typeFilter is updated directly', function () {
    $me = actingAsUser();
    Activity::factory()->for($me)->create(['type' => ActivityType::Email, 'subject' => 'An email']);
    Activity::factory()->for($me)->create(['type' => ActivityType::Meeting, 'subject' => 'A meeting']);

    Livewire::test(ActivityList::class)
        ->set('typeFilter', 'Email')
        ->assertSee('An email')
        ->assertDontSee('A meeting');
});

it('filters by contact id', function () {
    $me = actingAsUser();
    $a = Contact::factory()->for($me)->create(['first_name' => 'A']);
    $b = Contact::factory()->for($me)->create(['first_name' => 'B']);
    Activity::factory()->for($me)->for($a)->create(['subject' => 'Belongs to A']);
    Activity::factory()->for($me)->for($b)->create(['subject' => 'Belongs to B']);

    Livewire::test(ActivityList::class)
        ->set('contactFilter', $a->id)
        ->assertSee('Belongs to A')
        ->assertDontSee('Belongs to B');
});

it('filters by deal id', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();
    $dealA = Deal::factory()->for($me)->for($contact)->create(['title' => 'DealA']);
    $dealB = Deal::factory()->for($me)->for($contact)->create(['title' => 'DealB']);
    Activity::factory()->for($me)->for($contact)->state(['deal_id' => $dealA->id])->create(['subject' => 'For A']);
    Activity::factory()->for($me)->for($contact)->state(['deal_id' => $dealB->id])->create(['subject' => 'For B']);

    Livewire::test(ActivityList::class)
        ->set('dealFilter', $dealA->id)
        ->assertSee('For A')
        ->assertDontSee('For B');
});

it('opens the create modal with a clean form and current timestamp', function () {
    actingAsUser();

    Livewire::test(ActivityList::class)
        ->set('subject', 'Stale')
        ->call('create')
        ->assertSet('showForm', true)
        ->assertSet('subject', '')
        ->assertSet('type', 'Note')
        ->assertSet('editingActivityId', null);
});

it('creates a new activity', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();

    Livewire::test(ActivityList::class)
        ->call('create')
        ->set('type', 'Call')
        ->set('subject', 'Discovery')
        ->set('body', 'Talked about needs')
        ->set('contactId', $contact->id)
        ->call('save')
        ->assertSet('showForm', false)
        ->assertHasNoErrors();

    $activity = Activity::firstWhere('subject', 'Discovery');
    expect($activity)->not->toBeNull()
        ->and($activity->user_id)->toBe($me->id)
        ->and($activity->type)->toBe(ActivityType::Call)
        ->and($activity->body)->toBe('Talked about needs')
        ->and($activity->contact_id)->toBe($contact->id);
});

it('stores blank optional fields as null on create', function () {
    $me = actingAsUser();

    Livewire::test(ActivityList::class)
        ->set('type', 'Note')
        ->set('subject', 'No body')
        ->set('occurredAt', now()->format('Y-m-d\TH:i'))
        ->call('save')
        ->assertHasNoErrors();

    $activity = Activity::firstWhere('subject', 'No body');
    expect($activity->body)->toBeNull()
        ->and($activity->contact_id)->toBeNull()
        ->and($activity->deal_id)->toBeNull();
});

it('validates required fields', function () {
    actingAsUser();

    Livewire::test(ActivityList::class)
        ->call('save')
        ->assertHasErrors([
            'subject' => 'required',
            'occurredAt' => 'required',
        ]);
});

it('opens the edit modal pre-populated from the activity', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();
    $deal = Deal::factory()->for($me)->for($contact)->create();
    $activity = Activity::factory()->for($me)->for($contact)->state(['deal_id' => $deal->id])->create([
        'type' => ActivityType::Meeting,
        'subject' => 'Editable',
        'body' => 'old body',
        'occurred_at' => now(),
    ]);

    Livewire::test(ActivityList::class)
        ->call('edit', $activity->id)
        ->assertSet('showForm', true)
        ->assertSet('editingActivityId', $activity->id)
        ->assertSet('type', 'Meeting')
        ->assertSet('subject', 'Editable')
        ->assertSet('body', 'old body')
        ->assertSet('contactId', $contact->id)
        ->assertSet('dealId', $deal->id);
});

it('blanks the body field when editing an activity with a null body', function () {
    $me = actingAsUser();
    $activity = Activity::factory()->for($me)->create([
        'body' => null,
    ]);

    Livewire::test(ActivityList::class)
        ->call('edit', $activity->id)
        ->assertSet('body', '');
});

it('updates an existing activity', function () {
    $me = actingAsUser();
    $activity = Activity::factory()->for($me)->create(['subject' => 'Old']);

    Livewire::test(ActivityList::class)
        ->call('edit', $activity->id)
        ->set('subject', 'Updated')
        ->call('save')
        ->assertSet('showForm', false)
        ->assertHasNoErrors();

    expect($activity->fresh()->subject)->toBe('Updated');
});

it('forbids editing another user\'s activity', function () {
    actingAsUser();
    $other = User::factory()->create();
    $activity = Activity::factory()->for($other)->create();

    Livewire::test(ActivityList::class)
        ->call('edit', $activity->id)
        ->assertStatus(403);
});

it('forbids saving an edit to another user\'s activity', function () {
    actingAsUser();
    $other = User::factory()->create();
    $activity = Activity::factory()->for($other)->create(['subject' => 'Theirs']);

    Livewire::test(ActivityList::class)
        ->set('editingActivityId', $activity->id)
        ->set('type', 'Note')
        ->set('subject', 'Hijack')
        ->set('occurredAt', now()->format('Y-m-d\TH:i'))
        ->call('save')
        ->assertStatus(403);

    expect($activity->fresh()->subject)->toBe('Theirs');
});

it('opens the delete confirmation modal', function () {
    $me = actingAsUser();
    $activity = Activity::factory()->for($me)->create();

    Livewire::test(ActivityList::class)
        ->call('confirmDelete', $activity->id)
        ->assertSet('showDeleteConfirm', true)
        ->assertSet('deletingActivityId', $activity->id);
});

it('deletes an activity when authorized', function () {
    $me = actingAsUser();
    $activity = Activity::factory()->for($me)->create();

    Livewire::test(ActivityList::class)
        ->call('confirmDelete', $activity->id)
        ->call('delete')
        ->assertSet('showDeleteConfirm', false)
        ->assertSet('deletingActivityId', null);

    expect(Activity::find($activity->id))->toBeNull();
});

it('forbids deleting another user\'s activity', function () {
    actingAsUser();
    $other = User::factory()->create();
    $activity = Activity::factory()->for($other)->create();

    Livewire::test(ActivityList::class)
        ->set('deletingActivityId', $activity->id)
        ->call('delete')
        ->assertStatus(403);

    expect(Activity::find($activity->id))->not->toBeNull();
});
