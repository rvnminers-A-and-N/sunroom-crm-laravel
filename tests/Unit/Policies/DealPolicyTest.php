<?php

use App\Models\Deal;
use App\Models\User;
use App\Policies\DealPolicy;

beforeEach(function () {
    $this->policy = new DealPolicy;
    $this->owner = User::factory()->create();
    $this->other = User::factory()->create();
    $this->admin = User::factory()->admin()->create();
    $this->deal = Deal::factory()->for($this->owner)->create();
});

it('lets the owner view, update, and delete their own deal', function () {
    expect($this->policy->view($this->owner, $this->deal))->toBeTrue()
        ->and($this->policy->update($this->owner, $this->deal))->toBeTrue()
        ->and($this->policy->delete($this->owner, $this->deal))->toBeTrue();
});

it('blocks another regular user from viewing, updating, or deleting', function () {
    expect($this->policy->view($this->other, $this->deal))->toBeFalse()
        ->and($this->policy->update($this->other, $this->deal))->toBeFalse()
        ->and($this->policy->delete($this->other, $this->deal))->toBeFalse();
});

it('lets an admin view, update, and delete any deal', function () {
    expect($this->policy->view($this->admin, $this->deal))->toBeTrue()
        ->and($this->policy->update($this->admin, $this->deal))->toBeTrue()
        ->and($this->policy->delete($this->admin, $this->deal))->toBeTrue();
});
