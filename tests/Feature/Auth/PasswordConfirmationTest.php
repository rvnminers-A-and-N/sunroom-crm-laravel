<?php

use App\Models\User;
use Livewire\Volt\Volt;

it('renders the confirm password screen', function () {
    $this->actingAs(User::factory()->create())
        ->get('/confirm-password')
        ->assertStatus(200)
        ->assertSeeVolt('pages.auth.confirm-password');
});

it('confirms the password when the correct one is supplied', function () {
    $this->actingAs(User::factory()->create());

    Volt::test('pages.auth.confirm-password')
        ->set('password', 'password')
        ->call('confirmPassword')
        ->assertRedirect('/dashboard')
        ->assertHasNoErrors();
});

it('rejects the confirm password form when the password is wrong', function () {
    $this->actingAs(User::factory()->create());

    Volt::test('pages.auth.confirm-password')
        ->set('password', 'wrong-password')
        ->call('confirmPassword')
        ->assertNoRedirect()
        ->assertHasErrors('password');
});
