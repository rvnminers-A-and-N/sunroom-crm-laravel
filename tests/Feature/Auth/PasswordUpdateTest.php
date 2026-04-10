<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;

it('updates the password when the current password is correct', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Volt::test('profile.update-password-form')
        ->set('current_password', 'password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasNoErrors()
        ->assertNoRedirect();

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

it('rejects a password update when the current password is wrong', function () {
    $this->actingAs(User::factory()->create());

    Volt::test('profile.update-password-form')
        ->set('current_password', 'wrong-password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasErrors(['current_password'])
        ->assertNoRedirect();
});
