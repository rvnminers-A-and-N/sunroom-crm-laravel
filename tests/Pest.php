<?php

use App\Models\User;
use App\Services\OllamaService;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\DuskTestCase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature tests boot the full framework and run against the Postgres test
| database. Unit tests still extend the Laravel TestCase so we can use
| framework helpers (config, container, etc.) without repeating ourselves.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Unit');

pest()->extend(DuskTestCase::class)
    ->use(DatabaseTruncation::class)
    ->in('Browser');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| Shared test helpers. Prefer these over rewriting the same setup in every
| test file.
|
*/

/**
 * Create a regular user and log them in for the current test.
 */
function actingAsUser(array $attributes = []): User
{
    $user = User::factory()->create($attributes);
    test()->actingAs($user);

    return $user;
}

/**
 * Create an admin user and log them in for the current test.
 */
function actingAsAdmin(array $attributes = []): User
{
    $user = User::factory()->admin()->create($attributes);
    test()->actingAs($user);

    return $user;
}

/**
 * Intercept every outbound HTTP call from OllamaService with a deterministic
 * stub so tests never hit a real LLM. Callers may override the response body.
 */
function fakeOllama(array $responseBody = ['response' => 'fake ollama output']): void
{
    Http::fake([
        '*/api/generate' => Http::response($responseBody, 200),
    ]);

    config()->set('services.ollama.enabled', true);
    app()->forgetInstance(OllamaService::class);
}
