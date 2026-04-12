<?php

use App\Livewire\Companies\CompanyList;
use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use Livewire\Livewire;

it('redirects to login when not authenticated', function () {
    $this->get(route('companies.index'))->assertRedirect('/login');
});

it('renders only companies for the authenticated user', function () {
    $me = actingAsUser();
    $other = User::factory()->create();
    Company::factory()->for($me)->create(['name' => 'MyCo']);
    Company::factory()->for($other)->create(['name' => 'TheirCo']);

    Livewire::test(CompanyList::class)
        ->assertStatus(200)
        ->assertSee('MyCo')
        ->assertDontSee('TheirCo');
});

it('searches companies by name, industry and city', function () {
    $me = actingAsUser();
    Company::factory()->for($me)->create(['name' => 'Alpha Inc', 'industry' => 'Software', 'city' => 'Austin']);
    Company::factory()->for($me)->create(['name' => 'Beta LLC', 'industry' => 'Hardware', 'city' => 'Seattle']);
    Company::factory()->for($me)->create(['name' => 'Gamma Co', 'industry' => 'Retail', 'city' => 'Boston']);

    Livewire::test(CompanyList::class)
        ->set('search', 'Alpha')
        ->assertSee('Alpha Inc')
        ->assertDontSee('Beta LLC');

    Livewire::test(CompanyList::class)
        ->set('search', 'Hardware')
        ->assertSee('Beta LLC')
        ->assertDontSee('Alpha Inc');

    Livewire::test(CompanyList::class)
        ->set('search', 'Boston')
        ->assertSee('Gamma Co')
        ->assertDontSee('Alpha Inc');
});

it('toggles sort direction when sorting by the same field', function () {
    actingAsUser();

    Livewire::test(CompanyList::class)
        ->assertSet('sortField', 'name')
        ->assertSet('sortDirection', 'asc')
        ->call('sortBy', 'name')
        ->assertSet('sortDirection', 'desc')
        ->call('sortBy', 'name')
        ->assertSet('sortDirection', 'asc');
});

it('switches sort field and resets direction to asc', function () {
    actingAsUser();

    Livewire::test(CompanyList::class)
        ->call('sortBy', 'industry')
        ->assertSet('sortField', 'industry')
        ->assertSet('sortDirection', 'asc');
});

it('opens the create modal with a clean form', function () {
    actingAsUser();

    Livewire::test(CompanyList::class)
        ->set('name', 'Stale')
        ->call('create')
        ->assertSet('showForm', true)
        ->assertSet('name', '')
        ->assertSet('editingCompanyId', null);
});

it('creates a company with all fields populated', function () {
    $me = actingAsUser();

    Livewire::test(CompanyList::class)
        ->call('create')
        ->set('name', 'New Co')
        ->set('industry', 'Tech')
        ->set('website', 'https://new.example.com')
        ->set('phone', '555-0100')
        ->set('address', '1 Way')
        ->set('city', 'NYC')
        ->set('state', 'NY')
        ->set('zip', '10001')
        ->set('notes', 'a note')
        ->call('save')
        ->assertSet('showForm', false)
        ->assertHasNoErrors();

    $company = Company::firstWhere('name', 'New Co');
    expect($company)->not->toBeNull()
        ->and($company->user_id)->toBe($me->id)
        ->and($company->industry)->toBe('Tech')
        ->and($company->website)->toBe('https://new.example.com')
        ->and($company->city)->toBe('NYC');
});

it('stores blank optional fields as null', function () {
    actingAsUser();

    Livewire::test(CompanyList::class)
        ->set('name', 'Bare')
        ->call('save')
        ->assertHasNoErrors();

    $company = Company::firstWhere('name', 'Bare');
    expect($company->industry)->toBeNull()
        ->and($company->website)->toBeNull()
        ->and($company->phone)->toBeNull()
        ->and($company->address)->toBeNull()
        ->and($company->city)->toBeNull()
        ->and($company->state)->toBeNull()
        ->and($company->zip)->toBeNull()
        ->and($company->notes)->toBeNull();
});

it('validates required name on save', function () {
    actingAsUser();

    Livewire::test(CompanyList::class)
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

it('validates website url format on save', function () {
    actingAsUser();

    Livewire::test(CompanyList::class)
        ->set('name', 'Bad')
        ->set('website', 'not-a-url')
        ->call('save')
        ->assertHasErrors(['website' => 'url']);
});

it('opens the edit modal pre-populated from the company', function () {
    $me = actingAsUser();
    $company = Company::factory()->for($me)->create([
        'name' => 'Acme',
        'industry' => 'Manufacturing',
        'website' => 'https://acme.example.com',
        'phone' => '555',
        'address' => '1 Way',
        'city' => 'Springfield',
        'state' => 'IL',
        'zip' => '12345',
        'notes' => 'note',
    ]);

    Livewire::test(CompanyList::class)
        ->call('edit', $company->id)
        ->assertSet('showForm', true)
        ->assertSet('editingCompanyId', $company->id)
        ->assertSet('name', 'Acme')
        ->assertSet('industry', 'Manufacturing')
        ->assertSet('website', 'https://acme.example.com')
        ->assertSet('phone', '555')
        ->assertSet('address', '1 Way')
        ->assertSet('city', 'Springfield')
        ->assertSet('state', 'IL')
        ->assertSet('zip', '12345')
        ->assertSet('notes', 'note');
});

it('blanks out nullable fields when editing a sparse company', function () {
    $me = actingAsUser();
    $company = Company::factory()->for($me)->create([
        'name' => 'Bare',
        'industry' => null,
        'website' => null,
        'phone' => null,
        'address' => null,
        'city' => null,
        'state' => null,
        'zip' => null,
        'notes' => null,
    ]);

    Livewire::test(CompanyList::class)
        ->call('edit', $company->id)
        ->assertSet('industry', '')
        ->assertSet('website', '')
        ->assertSet('phone', '')
        ->assertSet('address', '')
        ->assertSet('city', '')
        ->assertSet('state', '')
        ->assertSet('zip', '')
        ->assertSet('notes', '');
});

it('updates an existing company', function () {
    $me = actingAsUser();
    $company = Company::factory()->for($me)->create(['name' => 'Old']);

    Livewire::test(CompanyList::class)
        ->call('edit', $company->id)
        ->set('name', 'Renamed')
        ->call('save')
        ->assertSet('showForm', false)
        ->assertHasNoErrors();

    expect($company->fresh()->name)->toBe('Renamed');
});

it('forbids editing a company owned by another user', function () {
    actingAsUser();
    $other = User::factory()->create();
    $company = Company::factory()->for($other)->create();

    Livewire::test(CompanyList::class)
        ->call('edit', $company->id)
        ->assertStatus(403);
});

it('forbids saving an edit to another user\'s company', function () {
    actingAsUser();
    $other = User::factory()->create();
    $company = Company::factory()->for($other)->create(['name' => 'Theirs']);

    Livewire::test(CompanyList::class)
        ->set('editingCompanyId', $company->id)
        ->set('name', 'Hijack')
        ->call('save')
        ->assertStatus(403);

    expect($company->fresh()->name)->toBe('Theirs');
});

it('opens the delete confirmation modal', function () {
    $me = actingAsUser();
    $company = Company::factory()->for($me)->create();

    Livewire::test(CompanyList::class)
        ->call('confirmDelete', $company->id)
        ->assertSet('showDeleteConfirm', true)
        ->assertSet('deletingCompanyId', $company->id);
});

it('deletes a company when authorized', function () {
    $me = actingAsUser();
    $company = Company::factory()->for($me)->create();

    Livewire::test(CompanyList::class)
        ->call('confirmDelete', $company->id)
        ->call('delete')
        ->assertSet('showDeleteConfirm', false)
        ->assertSet('deletingCompanyId', null);

    expect(Company::find($company->id))->toBeNull();
});

it('forbids deleting a company owned by another user', function () {
    actingAsUser();
    $other = User::factory()->create();
    $company = Company::factory()->for($other)->create();

    Livewire::test(CompanyList::class)
        ->set('deletingCompanyId', $company->id)
        ->call('delete')
        ->assertStatus(403);

    expect(Company::find($company->id))->not->toBeNull();
});

it('shows contact and deal counts in the listing', function () {
    $me = actingAsUser();
    $company = Company::factory()->for($me)->create(['name' => 'CountCo']);
    Contact::factory()->for($me)->for($company)->count(3)->create();

    Livewire::test(CompanyList::class)
        ->assertSee('CountCo');
});
