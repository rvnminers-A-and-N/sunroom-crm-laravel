<?php

use App\Enums\DealStage;
use App\Livewire\Deals\DealList;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Livewire\Livewire;

it('redirects to login when not authenticated', function () {
    $this->get(route('deals.index'))->assertRedirect('/login');
});

it('renders only deals belonging to the authenticated user', function () {
    $me = actingAsUser();
    $other = User::factory()->create();
    $myContact = Contact::factory()->for($me)->create();
    $otherContact = Contact::factory()->for($other)->create();
    Deal::factory()->for($me)->for($myContact)->create(['title' => 'Mine']);
    Deal::factory()->for($other)->for($otherContact)->create(['title' => 'Theirs']);

    Livewire::test(DealList::class)
        ->assertStatus(200)
        ->assertSee('Mine')
        ->assertDontSee('Theirs');
});

it('searches deals by title, contact name and company name', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create(['first_name' => 'Ada', 'last_name' => 'Lovelace']);
    $company = Company::factory()->for($me)->create(['name' => 'Acme']);
    Deal::factory()->for($me)->for($contact)->for($company)->create(['title' => 'Apollo']);
    Deal::factory()->for($me)->for($contact)->create(['title' => 'Zephyr']);

    Livewire::test(DealList::class)
        ->set('search', 'Apollo')
        ->assertSee('Apollo')
        ->assertDontSee('Zephyr');

    Livewire::test(DealList::class)
        ->set('search', 'Lovelace')
        ->assertSee('Apollo')
        ->assertSee('Zephyr');

    Livewire::test(DealList::class)
        ->set('search', 'Acme')
        ->assertSee('Apollo');
});

it('filters deals by stage', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();
    Deal::factory()->for($me)->for($contact)->create(['title' => 'LeadDeal', 'stage' => DealStage::Lead]);
    Deal::factory()->for($me)->for($contact)->create(['title' => 'WonDeal', 'stage' => DealStage::Won, 'closed_at' => now()]);

    Livewire::test(DealList::class)
        ->set('stageFilter', 'Lead')
        ->assertSee('LeadDeal')
        ->assertDontSee('WonDeal');
});

it('toggles sort direction when sorting by the same field', function () {
    actingAsUser();

    Livewire::test(DealList::class)
        ->assertSet('sortField', 'created_at')
        ->assertSet('sortDirection', 'desc')
        ->call('sortBy', 'title')
        ->assertSet('sortField', 'title')
        ->assertSet('sortDirection', 'asc')
        ->call('sortBy', 'title')
        ->assertSet('sortDirection', 'desc');
});

it('switches sort field and resets direction to asc', function () {
    actingAsUser();

    Livewire::test(DealList::class)
        ->call('sortBy', 'value')
        ->assertSet('sortField', 'value')
        ->assertSet('sortDirection', 'asc');
});

it('opens the create modal with a clean form', function () {
    actingAsUser();

    Livewire::test(DealList::class)
        ->set('title', 'Stale')
        ->call('create')
        ->assertSet('showForm', true)
        ->assertSet('title', '')
        ->assertSet('stage', 'Lead')
        ->assertSet('editingDealId', null);
});

it('creates a new lead deal without setting closed_at', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();

    Livewire::test(DealList::class)
        ->call('create')
        ->set('title', 'New Deal')
        ->set('value', '1000')
        ->set('stage', 'Lead')
        ->set('contactId', $contact->id)
        ->set('expectedCloseDate', '2026-12-31')
        ->set('notes', 'a note')
        ->call('save')
        ->assertSet('showForm', false)
        ->assertHasNoErrors();

    $deal = Deal::firstWhere('title', 'New Deal');
    expect($deal)->not->toBeNull()
        ->and($deal->user_id)->toBe($me->id)
        ->and($deal->stage)->toBe(DealStage::Lead)
        ->and($deal->closed_at)->toBeNull();
});

it('sets closed_at when creating a Won deal', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();

    Livewire::test(DealList::class)
        ->set('title', 'Won Deal')
        ->set('value', '5000')
        ->set('stage', 'Won')
        ->set('contactId', $contact->id)
        ->call('save')
        ->assertHasNoErrors();

    $deal = Deal::firstWhere('title', 'Won Deal');
    expect($deal->stage)->toBe(DealStage::Won)
        ->and($deal->closed_at)->not->toBeNull();
});

it('sets closed_at when creating a Lost deal', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();

    Livewire::test(DealList::class)
        ->set('title', 'Lost Deal')
        ->set('value', '300')
        ->set('stage', 'Lost')
        ->set('contactId', $contact->id)
        ->call('save')
        ->assertHasNoErrors();

    $deal = Deal::firstWhere('title', 'Lost Deal');
    expect($deal->closed_at)->not->toBeNull();
});

it('stores blank optional fields as null', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();

    Livewire::test(DealList::class)
        ->set('title', 'Bare Deal')
        ->set('value', '0')
        ->set('contactId', $contact->id)
        ->call('save')
        ->assertHasNoErrors();

    $deal = Deal::firstWhere('title', 'Bare Deal');
    expect($deal->expected_close_date)->toBeNull()
        ->and($deal->notes)->toBeNull()
        ->and($deal->company_id)->toBeNull();
});

it('validates required fields when saving', function () {
    actingAsUser();

    Livewire::test(DealList::class)
        ->call('save')
        ->assertHasErrors([
            'title' => 'required',
            'value' => 'required',
            'contactId' => 'required',
        ]);
});

it('validates that value must be numeric and non-negative', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();

    Livewire::test(DealList::class)
        ->set('title', 'Bad Value')
        ->set('value', '-5')
        ->set('contactId', $contact->id)
        ->call('save')
        ->assertHasErrors(['value' => 'min']);
});

it('opens the edit modal pre-populated from the deal', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();
    $company = Company::factory()->for($me)->create();
    $deal = Deal::factory()->for($me)->for($contact)->for($company)->create([
        'title' => 'Edit Me',
        'value' => 2500,
        'stage' => DealStage::Proposal,
        'expected_close_date' => '2026-06-30',
        'notes' => 'memo',
    ]);

    Livewire::test(DealList::class)
        ->call('edit', $deal->id)
        ->assertSet('showForm', true)
        ->assertSet('editingDealId', $deal->id)
        ->assertSet('title', 'Edit Me')
        ->assertSet('stage', 'Proposal')
        ->assertSet('contactId', $contact->id)
        ->assertSet('companyId', $company->id)
        ->assertSet('expectedCloseDate', '2026-06-30')
        ->assertSet('notes', 'memo');
});

it('blanks the date when editing a deal with no expected close date', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();
    $deal = Deal::factory()->for($me)->for($contact)->create([
        'expected_close_date' => null,
        'notes' => null,
    ]);

    Livewire::test(DealList::class)
        ->call('edit', $deal->id)
        ->assertSet('expectedCloseDate', '')
        ->assertSet('notes', '');
});

it('updates an existing deal and clears closed_at when transitioning out of Won', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();
    $deal = Deal::factory()->for($me)->for($contact)->create([
        'title' => 'Old Title',
        'stage' => DealStage::Won,
        'closed_at' => now()->subDays(3),
    ]);

    Livewire::test(DealList::class)
        ->call('edit', $deal->id)
        ->set('title', 'New Title')
        ->set('stage', 'Negotiation')
        ->call('save')
        ->assertSet('showForm', false)
        ->assertHasNoErrors();

    $fresh = $deal->fresh();
    expect($fresh->title)->toBe('New Title')
        ->and($fresh->stage)->toBe(DealStage::Negotiation)
        ->and($fresh->closed_at)->toBeNull();
});

it('forbids editing a deal owned by another user', function () {
    actingAsUser();
    $other = User::factory()->create();
    $contact = Contact::factory()->for($other)->create();
    $deal = Deal::factory()->for($other)->for($contact)->create();

    Livewire::test(DealList::class)
        ->call('edit', $deal->id)
        ->assertStatus(403);
});

it('forbids saving an edit to another user\'s deal', function () {
    actingAsUser();
    $other = User::factory()->create();
    $contact = Contact::factory()->for($other)->create();
    $deal = Deal::factory()->for($other)->for($contact)->create(['title' => 'Theirs']);

    Livewire::test(DealList::class)
        ->set('editingDealId', $deal->id)
        ->set('title', 'Hijack')
        ->set('value', '1')
        ->set('contactId', $contact->id)
        ->call('save')
        ->assertStatus(403);

    expect($deal->fresh()->title)->toBe('Theirs');
});

it('opens the delete confirmation modal', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();
    $deal = Deal::factory()->for($me)->for($contact)->create();

    Livewire::test(DealList::class)
        ->call('confirmDelete', $deal->id)
        ->assertSet('showDeleteConfirm', true)
        ->assertSet('deletingDealId', $deal->id);
});

it('deletes a deal when authorized', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();
    $deal = Deal::factory()->for($me)->for($contact)->create();

    Livewire::test(DealList::class)
        ->call('confirmDelete', $deal->id)
        ->call('delete')
        ->assertSet('showDeleteConfirm', false)
        ->assertSet('deletingDealId', null);

    expect(Deal::find($deal->id))->toBeNull();
});

it('forbids deleting a deal owned by another user', function () {
    actingAsUser();
    $other = User::factory()->create();
    $contact = Contact::factory()->for($other)->create();
    $deal = Deal::factory()->for($other)->for($contact)->create();

    Livewire::test(DealList::class)
        ->set('deletingDealId', $deal->id)
        ->call('delete')
        ->assertStatus(403);

    expect(Deal::find($deal->id))->not->toBeNull();
});
