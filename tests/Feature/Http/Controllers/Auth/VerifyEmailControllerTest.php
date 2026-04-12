<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

/*
|--------------------------------------------------------------------------
| VerifyEmailController
|--------------------------------------------------------------------------
|
| Breeze ships an EmailVerificationTest that walks the happy path through
| a signed verification URL. We round out the controller's coverage with
| the already-verified short-circuit branch so the early return is
| exercised explicitly.
|
*/

it('redirects to the dashboard without firing Verified when the user is already verified', function () {
    Event::fake([Verified::class]);

    $user = User::factory()->create(); // verified by default in the factory

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->actingAs($user)
        ->get($verificationUrl)
        ->assertRedirect(route('dashboard', absolute: false).'?verified=1');

    Event::assertNotDispatched(Verified::class);
});

it('marks the user as verified and dispatches the Verified event on first verification', function () {
    Event::fake([Verified::class]);

    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->actingAs($user)
        ->get($verificationUrl)
        ->assertRedirect(route('dashboard', absolute: false).'?verified=1');

    Event::assertDispatched(Verified::class, fn (Verified $event) => $event->user->is($user));

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});
