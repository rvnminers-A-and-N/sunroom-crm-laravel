<?php

use App\Models\Contact;
use App\Models\User;
use App\Policies\ContactPolicy;

beforeEach(function () {
    $this->policy = new ContactPolicy;
    $this->owner = User::factory()->create();
    $this->other = User::factory()->create();
    $this->admin = User::factory()->admin()->create();
    $this->contact = Contact::factory()->for($this->owner)->create();
});

it('lets the owner view, update, and delete their own contact', function () {
    expect($this->policy->view($this->owner, $this->contact))->toBeTrue()
        ->and($this->policy->update($this->owner, $this->contact))->toBeTrue()
        ->and($this->policy->delete($this->owner, $this->contact))->toBeTrue();
});

it('blocks another regular user from viewing, updating, or deleting', function () {
    expect($this->policy->view($this->other, $this->contact))->toBeFalse()
        ->and($this->policy->update($this->other, $this->contact))->toBeFalse()
        ->and($this->policy->delete($this->other, $this->contact))->toBeFalse();
});

it('lets an admin view, update, and delete any contact', function () {
    expect($this->policy->view($this->admin, $this->contact))->toBeTrue()
        ->and($this->policy->update($this->admin, $this->contact))->toBeTrue()
        ->and($this->policy->delete($this->admin, $this->contact))->toBeTrue();
});
