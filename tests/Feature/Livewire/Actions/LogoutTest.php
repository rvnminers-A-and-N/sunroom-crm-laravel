<?php

use App\Livewire\Actions\Logout;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| Logout action
|--------------------------------------------------------------------------
|
| The Logout action is the single source of truth for tearing down a
| user's session. It is invoked from the navigation Volt component but
| has no Livewire dependencies of its own, so we drive it directly to
| guarantee every line is covered.
|
*/

it('logs out the authenticated user, invalidates the session, and rotates the csrf token', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Session::start();
    Session::put('marker', 'before');
    $oldToken = Session::token();

    expect(Auth::check())->toBeTrue();

    (new Logout)();

    expect(Auth::check())->toBeFalse()
        ->and(Auth::guard('web')->user())->toBeNull()
        ->and(Session::get('marker'))->toBeNull()
        ->and(Session::token())->not->toBe($oldToken);
});

it('is safe to call when no user is authenticated', function () {
    expect(Auth::check())->toBeFalse();

    (new Logout)();

    expect(Auth::check())->toBeFalse();
});
