<?php

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| LoginForm
|--------------------------------------------------------------------------
|
| The Volt login page (`pages.auth.login`) embeds the LoginForm form
| object via `public LoginForm $form`. We exercise it through the page so
| every code path - successful auth, the failure branch that hits the
| rate limiter, the throttle key generation, and the lockout event - is
| reachable in coverage.
|
*/

it('authenticates a user with valid credentials and clears the rate limiter', function () {
    $user = User::factory()->create();

    RateLimiter::hit(Str::transliterate(Str::lower($user->email).'|127.0.0.1'), 60);

    Volt::test('pages.auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'password')
        ->set('form.remember', true)
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    expect(auth()->check())->toBeTrue()
        ->and(auth()->id())->toBe($user->id)
        ->and(RateLimiter::attempts(Str::transliterate(Str::lower($user->email).'|127.0.0.1')))->toBe(0);
});

it('records a failed attempt against the throttle key when credentials are wrong', function () {
    $user = User::factory()->create();
    $key = Str::transliterate(Str::lower($user->email).'|127.0.0.1');

    Volt::test('pages.auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'wrong-password')
        ->call('login')
        ->assertHasErrors(['form.email']);

    expect(auth()->check())->toBeFalse()
        ->and(RateLimiter::attempts($key))->toBe(1);
});

it('fires the Lockout event and throttles further attempts after five failures', function () {
    Event::fake([Lockout::class]);

    $user = User::factory()->create();
    $key = Str::transliterate(Str::lower($user->email).'|127.0.0.1');

    for ($i = 0; $i < 5; $i++) {
        RateLimiter::hit($key);
    }

    Volt::test('pages.auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'password')
        ->call('login')
        ->assertHasErrors(['form.email']);

    Event::assertDispatched(Lockout::class);
    expect(auth()->check())->toBeFalse();
});

it('lowercases and transliterates the email when building the throttle key', function () {
    $user = User::factory()->create(['email' => 'mixed.case@example.com']);
    $expectedKey = Str::transliterate('mixed.case@example.com|127.0.0.1');

    Volt::test('pages.auth.login')
        ->set('form.email', 'MIXED.CASE@example.com')
        ->set('form.password', 'wrong-password')
        ->call('login')
        ->assertHasErrors(['form.email']);

    expect(RateLimiter::attempts($expectedKey))->toBe(1);
});

it('validates the email and password fields before attempting authentication', function () {
    Volt::test('pages.auth.login')
        ->set('form.email', 'not-an-email')
        ->set('form.password', '')
        ->call('login')
        ->assertHasErrors([
            'form.email' => 'email',
            'form.password' => 'required',
        ]);

    expect(auth()->check())->toBeFalse();
});
