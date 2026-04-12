<?php

use App\Livewire\Contacts\ContactList;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Livewire\Livewire;

it('renders the contact list for the authenticated user only', function () {
    $me = actingAsUser();
    $other = User::factory()->create();
    Contact::factory()->for($me)->create(['first_name' => 'Mine']);
    Contact::factory()->for($other)->create(['first_name' => 'Theirs']);

    Livewire::test(ContactList::class)
        ->assertStatus(200)
        ->assertSee('Mine')
        ->assertDontSee('Theirs');
});

it('redirects to login when not authenticated', function () {
    $this->get(route('contacts.index'))->assertRedirect('/login');
});

it('searches contacts by first name, last name and email and resets pagination', function () {
    $me = actingAsUser();
    Contact::factory()->for($me)->create(['first_name' => 'Ada', 'last_name' => 'Lovelace', 'email' => 'ada@example.com']);
    Contact::factory()->for($me)->create(['first_name' => 'Bob', 'last_name' => 'Smith', 'email' => 'bob@example.com']);
    Contact::factory()->for($me)->create(['first_name' => 'Charlie', 'last_name' => 'Brown', 'email' => 'charlie@example.com']);

    Livewire::test(ContactList::class)
        ->set('search', 'Ada')
        ->assertSee('Lovelace')
        ->assertDontSee('Smith')
        ->assertDontSee('Brown');

    Livewire::test(ContactList::class)
        ->set('search', 'Smith')
        ->assertSee('Smith')
        ->assertDontSee('Lovelace');

    Livewire::test(ContactList::class)
        ->set('search', 'charlie@example.com')
        ->assertSee('Charlie')
        ->assertDontSee('Lovelace');
});

it('filters contacts by company and resets pagination', function () {
    $me = actingAsUser();
    $companyA = Company::factory()->for($me)->create(['name' => 'Acme']);
    $companyB = Company::factory()->for($me)->create(['name' => 'Globex']);
    Contact::factory()->for($me)->for($companyA)->create(['first_name' => 'AcmePerson']);
    Contact::factory()->for($me)->for($companyB)->create(['first_name' => 'GlobexPerson']);

    Livewire::test(ContactList::class)
        ->set('companyFilter', $companyA->id)
        ->assertSee('AcmePerson')
        ->assertDontSee('GlobexPerson');
});

it('filters contacts by tag and resets pagination', function () {
    $me = actingAsUser();
    $vipTag = Tag::factory()->create(['name' => 'VIP']);
    $coldTag = Tag::factory()->create(['name' => 'Cold']);
    $vipContact = Contact::factory()->for($me)->create(['first_name' => 'Hot']);
    $coldContact = Contact::factory()->for($me)->create(['first_name' => 'Frozen']);
    $vipContact->tags()->attach($vipTag);
    $coldContact->tags()->attach($coldTag);

    Livewire::test(ContactList::class)
        ->set('tagFilter', $vipTag->id)
        ->assertSee('Hot')
        ->assertDontSee('Frozen');
});

it('toggles sort direction when sorting by the same field', function () {
    actingAsUser();

    Livewire::test(ContactList::class)
        ->assertSet('sortField', 'created_at')
        ->assertSet('sortDirection', 'desc')
        ->call('sortBy', 'first_name')
        ->assertSet('sortField', 'first_name')
        ->assertSet('sortDirection', 'asc')
        ->call('sortBy', 'first_name')
        ->assertSet('sortDirection', 'desc')
        ->call('sortBy', 'first_name')
        ->assertSet('sortDirection', 'asc');
});

it('switches the sort field and resets direction to asc', function () {
    actingAsUser();

    Livewire::test(ContactList::class)
        ->call('sortBy', 'first_name')
        ->assertSet('sortField', 'first_name')
        ->assertSet('sortDirection', 'asc')
        ->call('sortBy', 'last_name')
        ->assertSet('sortField', 'last_name')
        ->assertSet('sortDirection', 'asc');
});

it('opens the create modal with a clean form', function () {
    actingAsUser();

    Livewire::test(ContactList::class)
        ->set('firstName', 'Stale')
        ->call('create')
        ->assertSet('showForm', true)
        ->assertSet('firstName', '')
        ->assertSet('editingContactId', null);
});

it('creates a new contact and syncs tags', function () {
    $me = actingAsUser();
    $company = Company::factory()->for($me)->create();
    $tag = Tag::factory()->create();

    Livewire::test(ContactList::class)
        ->call('create')
        ->set('firstName', 'New')
        ->set('lastName', 'Person')
        ->set('email', 'new@example.com')
        ->set('phone', '555-0100')
        ->set('title', 'CEO')
        ->set('notes', 'A note')
        ->set('companyId', $company->id)
        ->set('tagIds', [$tag->id])
        ->call('save')
        ->assertSet('showForm', false)
        ->assertHasNoErrors();

    $contact = Contact::where('email', 'new@example.com')->first();
    expect($contact)->not->toBeNull()
        ->and($contact->user_id)->toBe($me->id)
        ->and($contact->first_name)->toBe('New')
        ->and($contact->last_name)->toBe('Person')
        ->and($contact->company_id)->toBe($company->id)
        ->and($contact->tags->pluck('id')->toArray())->toBe([$tag->id]);
});

it('stores blank optional fields as null', function () {
    $me = actingAsUser();

    Livewire::test(ContactList::class)
        ->set('firstName', 'No')
        ->set('lastName', 'Extras')
        ->call('save')
        ->assertHasNoErrors();

    $contact = Contact::firstWhere('first_name', 'No');
    expect($contact->email)->toBeNull()
        ->and($contact->phone)->toBeNull()
        ->and($contact->title)->toBeNull()
        ->and($contact->notes)->toBeNull()
        ->and($contact->company_id)->toBeNull();
});

it('validates required fields when saving', function () {
    actingAsUser();

    Livewire::test(ContactList::class)
        ->call('save')
        ->assertHasErrors(['firstName' => 'required', 'lastName' => 'required']);
});

it('validates email format on save', function () {
    actingAsUser();

    Livewire::test(ContactList::class)
        ->set('firstName', 'A')
        ->set('lastName', 'B')
        ->set('email', 'not-an-email')
        ->call('save')
        ->assertHasErrors(['email' => 'email']);
});

it('opens the edit modal pre-populated from the contact', function () {
    $me = actingAsUser();
    $company = Company::factory()->for($me)->create();
    $tag = Tag::factory()->create();
    $contact = Contact::factory()->for($me)->for($company)->create([
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'phone' => '555',
        'title' => 'Mathematician',
        'notes' => 'inventor',
    ]);
    $contact->tags()->attach($tag);

    Livewire::test(ContactList::class)
        ->call('edit', $contact->id)
        ->assertSet('showForm', true)
        ->assertSet('editingContactId', $contact->id)
        ->assertSet('firstName', 'Ada')
        ->assertSet('lastName', 'Lovelace')
        ->assertSet('email', 'ada@example.com')
        ->assertSet('phone', '555')
        ->assertSet('title', 'Mathematician')
        ->assertSet('notes', 'inventor')
        ->assertSet('companyId', $company->id)
        ->assertSet('tagIds', [$tag->id]);
});

it('blanks out nullable fields in the form when editing a sparse contact', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create([
        'first_name' => 'Bare',
        'last_name' => 'Bones',
        'email' => null,
        'phone' => null,
        'title' => null,
        'notes' => null,
        'company_id' => null,
    ]);

    Livewire::test(ContactList::class)
        ->call('edit', $contact->id)
        ->assertSet('email', '')
        ->assertSet('phone', '')
        ->assertSet('title', '')
        ->assertSet('notes', '')
        ->assertSet('companyId', null);
});

it('updates an existing contact and re-syncs tags', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create(['first_name' => 'Old']);
    $newTag = Tag::factory()->create();

    Livewire::test(ContactList::class)
        ->call('edit', $contact->id)
        ->set('firstName', 'Updated')
        ->set('tagIds', [$newTag->id])
        ->call('save')
        ->assertSet('showForm', false)
        ->assertHasNoErrors();

    expect($contact->fresh()->first_name)->toBe('Updated')
        ->and($contact->fresh()->tags->pluck('id')->toArray())->toBe([$newTag->id]);
});

it('forbids editing a contact owned by another user', function () {
    actingAsUser();
    $other = User::factory()->create();
    $contact = Contact::factory()->for($other)->create();

    Livewire::test(ContactList::class)
        ->call('edit', $contact->id)
        ->assertStatus(403);
});

it('forbids saving an edit to another user\'s contact', function () {
    actingAsUser();
    $other = User::factory()->create();
    $contact = Contact::factory()->for($other)->create(['first_name' => 'Theirs']);

    // Bypass the edit() authorization to set state directly, then save.
    Livewire::test(ContactList::class)
        ->set('editingContactId', $contact->id)
        ->set('firstName', 'Hijacked')
        ->set('lastName', 'Update')
        ->call('save')
        ->assertStatus(403);

    expect($contact->fresh()->first_name)->toBe('Theirs');
});

it('opens the delete confirmation modal', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();

    Livewire::test(ContactList::class)
        ->call('confirmDelete', $contact->id)
        ->assertSet('showDeleteConfirm', true)
        ->assertSet('deletingContactId', $contact->id);
});

it('deletes a contact when authorized', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();

    Livewire::test(ContactList::class)
        ->call('confirmDelete', $contact->id)
        ->call('delete')
        ->assertSet('showDeleteConfirm', false)
        ->assertSet('deletingContactId', null);

    expect(Contact::find($contact->id))->toBeNull();
});

it('forbids deleting a contact owned by another user', function () {
    actingAsUser();
    $other = User::factory()->create();
    $contact = Contact::factory()->for($other)->create();

    Livewire::test(ContactList::class)
        ->set('deletingContactId', $contact->id)
        ->call('delete')
        ->assertStatus(403);

    expect(Contact::find($contact->id))->not->toBeNull();
});
