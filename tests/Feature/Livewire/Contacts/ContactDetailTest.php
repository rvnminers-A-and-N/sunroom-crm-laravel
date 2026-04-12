<?php

use App\Livewire\Contacts\ContactDetail;
use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

it('redirects to login when not authenticated', function () {
    $contact = Contact::factory()->for(User::factory())->create();
    $this->get(route('contacts.show', $contact->id))->assertRedirect('/login');
});

it('mounts and renders a contact owned by the authenticated user', function () {
    $me = actingAsUser();
    $company = Company::factory()->for($me)->create(['name' => 'Acme']);
    $tag = Tag::factory()->create(['name' => 'VIP']);
    $contact = Contact::factory()->for($me)->for($company)->create([
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
    ]);
    $contact->tags()->attach($tag);
    Deal::factory()->for($me)->for($contact)->for($company)->create(['title' => 'Big Deal']);
    Activity::factory()->for($me)->for($contact)->create(['subject' => 'Recent call']);

    Livewire::test(ContactDetail::class, ['id' => $contact->id])
        ->assertStatus(200)
        ->assertSet('contact.id', $contact->id)
        ->assertSee('Ada')
        ->assertSee('Lovelace')
        ->assertSee('Big Deal')
        ->assertSee('Recent call');
});

it('returns 404 for a non-existent contact', function () {
    actingAsUser();

    Livewire::test(ContactDetail::class, ['id' => 999999]);
})->throws(ModelNotFoundException::class);

it('forbids viewing a contact owned by another user', function () {
    actingAsUser();
    $other = User::factory()->create();
    $contact = Contact::factory()->for($other)->create();

    Livewire::test(ContactDetail::class, ['id' => $contact->id])
        ->assertStatus(403);
});

it('lets an admin view any contact', function () {
    actingAsAdmin();
    $other = User::factory()->create();
    $contact = Contact::factory()->for($other)->create(['first_name' => 'Borrowed']);

    Livewire::test(ContactDetail::class, ['id' => $contact->id])
        ->assertStatus(200)
        ->assertSee('Borrowed');
});
