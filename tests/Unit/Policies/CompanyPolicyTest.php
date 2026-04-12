<?php

use App\Models\Company;
use App\Models\User;
use App\Policies\CompanyPolicy;

beforeEach(function () {
    $this->policy = new CompanyPolicy;
    $this->owner = User::factory()->create();
    $this->other = User::factory()->create();
    $this->admin = User::factory()->admin()->create();
    $this->company = Company::factory()->for($this->owner)->create();
});

it('lets the owner view, update, and delete their own company', function () {
    expect($this->policy->view($this->owner, $this->company))->toBeTrue()
        ->and($this->policy->update($this->owner, $this->company))->toBeTrue()
        ->and($this->policy->delete($this->owner, $this->company))->toBeTrue();
});

it('blocks another regular user from viewing, updating, or deleting', function () {
    expect($this->policy->view($this->other, $this->company))->toBeFalse()
        ->and($this->policy->update($this->other, $this->company))->toBeFalse()
        ->and($this->policy->delete($this->other, $this->company))->toBeFalse();
});

it('lets an admin view, update, and delete any company', function () {
    expect($this->policy->view($this->admin, $this->company))->toBeTrue()
        ->and($this->policy->update($this->admin, $this->company))->toBeTrue()
        ->and($this->policy->delete($this->admin, $this->company))->toBeTrue();
});
