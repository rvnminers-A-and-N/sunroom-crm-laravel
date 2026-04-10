<?php

use App\Models\User;
use Livewire\Volt\Volt;

it('renders the login screen', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSeeVolt('pages.auth.login');
});

it('lets users authenticate from the login screen', function () {
    $user = User::factory()->create();

    Volt::test('pages.auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

it('rejects login attempts with an invalid password', function () {
    $user = User::factory()->create();

    Volt::test('pages.auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'wrong-password')
        ->call('login')
        ->assertHasErrors()
        ->assertNoRedirect();

    $this->assertGuest();
});

it('renders the navigation menu for authenticated users', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/dashboard')
        ->assertOk()
        ->assertSeeVolt('layout.navigation');
});

it('lets users log out from the navigation component', function () {
    $this->actingAs(User::factory()->create());

    Volt::test('layout.navigation')
        ->call('logout')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
});
