<?php

use App\Livewire\Companies\CompanyDetail;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

it('redirects to login when not authenticated', function () {
    $company = Company::factory()->for(User::factory())->create();
    $this->get(route('companies.show', $company->id))->assertRedirect('/login');
});

it('mounts and renders a company owned by the authenticated user', function () {
    $me = actingAsUser();
    $company = Company::factory()->for($me)->create(['name' => 'Acme Corp']);
    $contact = Contact::factory()->for($me)->for($company)->create(['first_name' => 'Ada']);
    Deal::factory()->for($me)->for($contact)->for($company)->create(['title' => 'Big Deal']);

    Livewire::test(CompanyDetail::class, ['id' => $company->id])
        ->assertStatus(200)
        ->assertSet('company.id', $company->id)
        ->assertSee('Acme Corp')
        ->assertSee('Ada')
        ->assertSee('Big Deal');
});

it('throws ModelNotFoundException for a non-existent company', function () {
    actingAsUser();

    Livewire::test(CompanyDetail::class, ['id' => 999999]);
})->throws(ModelNotFoundException::class);

it('forbids viewing a company owned by another user', function () {
    actingAsUser();
    $other = User::factory()->create();
    $company = Company::factory()->for($other)->create();

    Livewire::test(CompanyDetail::class, ['id' => $company->id])
        ->assertStatus(403);
});

it('lets an admin view any company', function () {
    actingAsAdmin();
    $other = User::factory()->create();
    $company = Company::factory()->for($other)->create(['name' => 'Borrowed Corp']);

    Livewire::test(CompanyDetail::class, ['id' => $company->id])
        ->assertStatus(200)
        ->assertSee('Borrowed Corp');
});
