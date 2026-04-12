<?php

use App\Livewire\Settings;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

it('redirects to login when not authenticated', function () {
    $this->get(route('settings'))->assertRedirect('/login');
});

it('mounts with the current user details', function () {
    $me = actingAsUser(['name' => 'My Name', 'email' => 'me@example.com']);

    Livewire::test(Settings::class)
        ->assertStatus(200)
        ->assertSet('tab', 'profile')
        ->assertSet('name', 'My Name')
        ->assertSet('email', 'me@example.com');
});

it('switches the active tab', function () {
    actingAsUser();

    Livewire::test(Settings::class)
        ->call('setTab', 'password')
        ->assertSet('tab', 'password')
        ->call('setTab', 'tags')
        ->assertSet('tab', 'tags');
});

it('updates the profile name and email', function () {
    $me = actingAsUser(['name' => 'Old', 'email' => 'old@example.com']);

    Livewire::test(Settings::class)
        ->set('name', 'New Name')
        ->set('email', 'new@example.com')
        ->call('updateProfile')
        ->assertHasNoErrors();

    $fresh = $me->fresh();
    expect($fresh->name)->toBe('New Name')
        ->and($fresh->email)->toBe('new@example.com')
        ->and($fresh->email_verified_at)->toBeNull();
});

it('keeps email_verified_at when updating only the name', function () {
    $verifiedAt = now();
    $me = actingAsUser(['email_verified_at' => $verifiedAt]);

    Livewire::test(Settings::class)
        ->set('name', 'Renamed')
        ->call('updateProfile')
        ->assertHasNoErrors();

    expect($me->fresh()->email_verified_at)->not->toBeNull();
});

it('validates the profile fields', function () {
    actingAsUser();

    Livewire::test(Settings::class)
        ->set('name', '')
        ->set('email', 'not-an-email')
        ->call('updateProfile')
        ->assertHasErrors(['name' => 'required', 'email' => 'email']);
});

it('rejects an email that already belongs to another user', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    actingAsUser();

    Livewire::test(Settings::class)
        ->set('email', 'taken@example.com')
        ->call('updateProfile')
        ->assertHasErrors(['email' => 'unique']);
});

it('updates the password when the current one matches', function () {
    $me = actingAsUser(['password' => Hash::make('current-pw')]);

    Livewire::test(Settings::class)
        ->set('currentPassword', 'current-pw')
        ->set('newPassword', 'new-password-1')
        ->set('newPasswordConfirmation', 'new-password-1')
        ->call('updatePassword')
        ->assertHasNoErrors()
        ->assertSet('currentPassword', '')
        ->assertSet('newPassword', '')
        ->assertSet('newPasswordConfirmation', '');

    expect(Hash::check('new-password-1', $me->fresh()->password))->toBeTrue();
});

it('rejects an incorrect current password', function () {
    actingAsUser(['password' => Hash::make('correct-pw')]);

    Livewire::test(Settings::class)
        ->set('currentPassword', 'wrong-pw')
        ->set('newPassword', 'new-password-1')
        ->set('newPasswordConfirmation', 'new-password-1')
        ->call('updatePassword')
        ->assertHasErrors(['currentPassword' => 'current_password']);
});

it('rejects when the new password and confirmation do not match', function () {
    actingAsUser(['password' => Hash::make('current-pw')]);

    Livewire::test(Settings::class)
        ->set('currentPassword', 'current-pw')
        ->set('newPassword', 'new-password-1')
        ->set('newPasswordConfirmation', 'different')
        ->call('updatePassword')
        ->assertHasErrors(['newPassword' => 'same']);
});

it('rejects a new password shorter than 8 characters', function () {
    actingAsUser(['password' => Hash::make('current-pw')]);

    Livewire::test(Settings::class)
        ->set('currentPassword', 'current-pw')
        ->set('newPassword', 'short')
        ->set('newPasswordConfirmation', 'short')
        ->call('updatePassword')
        ->assertHasErrors(['newPassword' => 'min']);
});

it('opens the create tag form with default color', function () {
    actingAsUser();

    Livewire::test(Settings::class)
        ->set('tagName', 'leftover')
        ->call('createTag')
        ->assertSet('showTagForm', true)
        ->assertSet('editingTagId', null)
        ->assertSet('tagName', '')
        ->assertSet('tagColor', '#02795F');
});

it('creates a new tag', function () {
    actingAsUser();

    Livewire::test(Settings::class)
        ->call('createTag')
        ->set('tagName', 'Hot')
        ->set('tagColor', '#FF0000')
        ->call('saveTag')
        ->assertSet('showTagForm', false)
        ->assertHasNoErrors();

    expect(Tag::firstWhere('name', 'Hot'))->not->toBeNull();
});

it('validates required tag name and color format', function () {
    actingAsUser();

    Livewire::test(Settings::class)
        ->call('createTag')
        ->set('tagName', '')
        ->set('tagColor', 'red')
        ->call('saveTag')
        ->assertHasErrors(['tagName' => 'required', 'tagColor' => 'regex']);
});

it('rejects a tag name that is already taken', function () {
    Tag::factory()->create(['name' => 'Taken']);
    actingAsUser();

    Livewire::test(Settings::class)
        ->call('createTag')
        ->set('tagName', 'Taken')
        ->set('tagColor', '#FFFFFF')
        ->call('saveTag')
        ->assertHasErrors(['tagName' => 'unique']);
});

it('opens the edit tag form pre-populated', function () {
    actingAsUser();
    $tag = Tag::factory()->create(['name' => 'EditMe', 'color' => '#123456']);

    Livewire::test(Settings::class)
        ->call('editTag', $tag->id)
        ->assertSet('showTagForm', true)
        ->assertSet('editingTagId', $tag->id)
        ->assertSet('tagName', 'EditMe')
        ->assertSet('tagColor', '#123456');
});

it('updates an existing tag and ignores its own name in the unique check', function () {
    actingAsUser();
    $tag = Tag::factory()->create(['name' => 'Original', 'color' => '#000000']);

    Livewire::test(Settings::class)
        ->call('editTag', $tag->id)
        ->set('tagName', 'Original')
        ->set('tagColor', '#FFFFFF')
        ->call('saveTag')
        ->assertHasNoErrors();

    expect($tag->fresh()->color)->toBe('#FFFFFF');
});

it('opens the delete confirmation modal for a tag', function () {
    actingAsUser();
    $tag = Tag::factory()->create();

    Livewire::test(Settings::class)
        ->call('confirmDeleteTag', $tag->id)
        ->assertSet('showDeleteConfirm', true)
        ->assertSet('deletingTagId', $tag->id);
});

it('deletes a tag', function () {
    actingAsUser();
    $tag = Tag::factory()->create();

    Livewire::test(Settings::class)
        ->call('confirmDeleteTag', $tag->id)
        ->call('deleteTag')
        ->assertSet('showDeleteConfirm', false)
        ->assertSet('deletingTagId', null);

    expect(Tag::find($tag->id))->toBeNull();
});
