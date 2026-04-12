<?php

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Dashboard;
use Tests\Browser\Pages\Login;

it('lets a registered user authenticate from the login screen', function () {
    $user = User::factory()->create([
        'email' => 'dusk-login@example.com',
    ]);

    $this->browse(function (Browser $browser) use ($user) {
        $browser->visit(new Login)
            ->type('@email', $user->email)
            ->type('@password', 'password')
            ->click('@submit')
            ->on(new Dashboard)
            ->assertSee('Total Contacts');
    });
});

it('shows an inline error when the password is wrong', function () {
    User::factory()->create(['email' => 'dusk-bad@example.com']);

    $this->browse(function (Browser $browser) {
        $browser->visit(new Login)
            ->type('@email', 'dusk-bad@example.com')
            ->type('@password', 'definitely-wrong')
            ->click('@submit')
            ->waitForText('credentials do not match', 5)
            ->assertPathIs('/login');
    });
});

it('redirects unauthenticated users away from the dashboard', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/dashboard')
            ->assertPathIs('/login');
    });
});
