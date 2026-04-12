<?php

use App\Models\User;
use Laravel\Dusk\Browser;

it('serves a 403 page when a non-admin visits the admin users route', function () {
    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/admin/users')
            ->assertSee('403');
    });
});

it('lets an admin reach the user management page', function () {
    $admin = User::factory()->admin()->create();

    $this->browse(function (Browser $browser) use ($admin) {
        $browser->loginAs($admin)
            ->visit('/admin/users')
            ->waitForText('User Management')
            ->assertPathIs('/admin/users');
    });
});

it('does not show the Users sidebar link to a non-admin', function () {
    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/dashboard')
            ->waitForText('Total Contacts')
            ->within('aside', function (Browser $sidebar) {
                $sidebar->assertDontSee('Users');
            });
    });
});
