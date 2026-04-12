<?php

use App\Models\User;
use Laravel\Dusk\Browser;

it('navigates the sidebar between dashboard, contacts, deals, and settings', function () {
    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/dashboard')
            ->waitForText('Total Contacts')
            ->assertPresent('aside a[href$="/contacts"]')
            ->assertPresent('aside a[href$="/deals"]')
            ->assertPresent('aside a[href$="/settings"]');

        $browser->visit('/contacts')
            ->waitForText('No contacts found')
            ->visit('/deals')
            ->waitForText('No deals found')
            ->visit('/settings')
            ->waitForText('Profile Information');
    });
});

it('logs the user out from the user dropdown', function () {
    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/dashboard')
            ->waitForText('Total Contacts')
            ->script("Livewire.getByName('layout.navigation')[0].call('logout');");

        $browser->waitForLocation('/')
            ->assertGuest();
    });
});
