<?php

use App\Models\Contact;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Contacts;

it('creates, edits, and deletes a contact through the modal flow', function () {
    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit(new Contacts)
            ->waitForText('No contacts found')
            ->click('main button[wire\\:click="create"]')
            ->waitFor('#firstName')
            ->type('#firstName', 'Aurora')
            ->type('#lastName', 'Borealis')
            ->type('#email', 'aurora@example.com')
            ->type('#phone', '555-0100')
            ->type('#title', 'Astronomer')
            ->click('button[type="submit"]')
            ->waitForText('Aurora Borealis')
            ->assertSee('aurora@example.com');

        expect(Contact::where('email', 'aurora@example.com')->exists())->toBeTrue();
    });

    $contact = Contact::where('email', 'aurora@example.com')->firstOrFail();

    $this->browse(function (Browser $browser) use ($user, $contact) {
        $browser->loginAs($user)
            ->visit(new Contacts)
            ->waitForText('Aurora Borealis')
            ->click("button[wire\\:click=\"edit({$contact->id})\"]")
            ->waitFor('#firstName')
            ->assertInputValue('#firstName', 'Aurora')
            ->clear('#title')
            ->type('#title', 'Lead Astronomer')
            ->click('button[type="submit"]')
            ->waitUntilMissing('#firstName', 5);

        expect($contact->fresh()->title)->toBe('Lead Astronomer');
    });

    $this->browse(function (Browser $browser) use ($user, $contact) {
        $browser->loginAs($user)
            ->visit(new Contacts)
            ->waitForText('Aurora Borealis')
            ->click("button[wire\\:click=\"confirmDelete({$contact->id})\"]")
            ->waitForText('Are you sure')
            ->click('button[wire\\:click="delete"]')
            ->waitForText('No contacts found');

        expect(Contact::find($contact->id))->toBeNull();
    });
});

it('shows inline validation errors when required fields are blank', function () {
    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit(new Contacts)
            ->waitForText('No contacts found')
            ->click('main button[wire\\:click="create"]')
            ->waitFor('#firstName')
            ->script("Livewire.getByName('contacts.contact-list')[0].call('save');");

        $browser->waitForText('first name field is required', 5)
            ->assertSee('first name field is required')
            ->assertSee('last name field is required');
    });
});
