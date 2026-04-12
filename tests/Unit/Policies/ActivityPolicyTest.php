<?php

use App\Models\Activity;
use App\Models\User;
use App\Policies\ActivityPolicy;

beforeEach(function () {
    $this->policy = new ActivityPolicy;
    $this->owner = User::factory()->create();
    $this->other = User::factory()->create();
    $this->admin = User::factory()->admin()->create();
    $this->activity = Activity::factory()->for($this->owner)->create();
});

it('lets the owner view, update, and delete their own activity', function () {
    expect($this->policy->view($this->owner, $this->activity))->toBeTrue()
        ->and($this->policy->update($this->owner, $this->activity))->toBeTrue()
        ->and($this->policy->delete($this->owner, $this->activity))->toBeTrue();
});

it('blocks another regular user from viewing, updating, or deleting', function () {
    expect($this->policy->view($this->other, $this->activity))->toBeFalse()
        ->and($this->policy->update($this->other, $this->activity))->toBeFalse()
        ->and($this->policy->delete($this->other, $this->activity))->toBeFalse();
});

it('lets an admin view, update, and delete any activity', function () {
    expect($this->policy->view($this->admin, $this->activity))->toBeTrue()
        ->and($this->policy->update($this->admin, $this->activity))->toBeTrue()
        ->and($this->policy->delete($this->admin, $this->activity))->toBeTrue();
});
