<?php

use App\Enums\ActivityType;
use App\Enums\DealStage;
use App\Models\Activity;
use App\Models\Deal;
use App\Services\OllamaService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

function makeOllamaService(array $overrides = []): OllamaService
{
    config()->set('services.ollama.enabled', $overrides['enabled'] ?? true);
    config()->set('services.ollama.base_url', $overrides['base_url'] ?? 'http://ollama.test/');
    config()->set('services.ollama.model', $overrides['model'] ?? 'test-model');
    app()->forgetInstance(OllamaService::class);

    return new OllamaService;
}

it('reports the enabled flag from config', function () {
    expect(makeOllamaService(['enabled' => true])->isEnabled())->toBeTrue()
        ->and(makeOllamaService(['enabled' => false])->isEnabled())->toBeFalse();
});

it('returns the disabled message when the service is disabled', function () {
    Http::fake();

    $result = makeOllamaService(['enabled' => false])->summarize('hello');

    expect($result)->toBe('AI features are disabled. Set OLLAMA_ENABLED=true in your .env to enable them.');
    Http::assertNothingSent();
});

it('summarizes text by sending a prompt and returning the response', function () {
    Http::fake([
        '*/api/generate' => Http::response(['response' => 'short summary'], 200),
    ]);

    $result = makeOllamaService()->summarize('Long activity notes.');

    expect($result)->toBe('short summary');
    Http::assertSent(function ($request) {
        return $request->url() === 'http://ollama.test/api/generate'
            && $request['model'] === 'test-model'
            && $request['stream'] === false
            && str_contains($request['prompt'], 'Long activity notes.');
    });
});

it('strips a trailing slash from the base url', function () {
    Http::fake([
        '*/api/generate' => Http::response(['response' => 'ok'], 200),
    ]);

    makeOllamaService(['base_url' => 'http://ollama.test/'])->summarize('hi');

    Http::assertSent(fn ($request) => $request->url() === 'http://ollama.test/api/generate');
});

it('generates deal insights with deal context and activity list', function () {
    Http::fake([
        '*/api/generate' => Http::response(['response' => 'next steps'], 200),
    ]);

    $deal = (new Deal)->forceFill([
        'title' => 'Big Deal',
        'value' => 12345.67,
        'stage' => DealStage::Proposal,
    ]);

    $activities = collect([
        (new Activity)->forceFill(['type' => ActivityType::Call, 'subject' => 'Discovery', 'body' => 'Talked about needs']),
        (new Activity)->forceFill(['type' => ActivityType::Note, 'subject' => 'Follow-up', 'body' => null]),
    ]);

    $result = makeOllamaService()->generateDealInsights($deal, $activities);

    expect($result)->toBe('next steps');
    Http::assertSent(function ($request) {
        return str_contains($request['prompt'], 'Big Deal')
            && str_contains($request['prompt'], '12,345.67')
            && str_contains($request['prompt'], DealStage::Proposal->value)
            && str_contains($request['prompt'], '[Call] Discovery: Talked about needs')
            && str_contains($request['prompt'], '[Note] Follow-up: No details');
    });
});

it('builds an ask prompt without context', function () {
    Http::fake([
        '*/api/generate' => Http::response(['response' => 'answer'], 200),
    ]);

    $result = makeOllamaService()->ask('What is a CRM?');

    expect($result)->toBe('answer');
    Http::assertSent(function ($request) {
        return str_contains($request['prompt'], 'What is a CRM?')
            && ! str_contains($request['prompt'], 'Context:');
    });
});

it('builds an ask prompt with context', function () {
    Http::fake([
        '*/api/generate' => Http::response(['response' => 'answer'], 200),
    ]);

    makeOllamaService()->ask('What is this?', 'Some context');

    Http::assertSent(function ($request) {
        return str_contains($request['prompt'], 'Context:')
            && str_contains($request['prompt'], 'Some context')
            && str_contains($request['prompt'], 'Question: What is this?');
    });
});

it('runs smartSearch with contact and activity context', function () {
    Http::fake([
        '*/api/generate' => Http::response(['response' => 'interpretation'], 200),
    ]);

    $contacts = collect([
        (object) ['id' => 1, 'first_name' => 'Ada', 'last_name' => 'Lovelace', 'email' => 'ada@example.com', 'company' => (object) ['name' => 'Acme']],
        (object) ['id' => 2, 'first_name' => 'Bob', 'last_name' => 'Smith', 'email' => null, 'company' => null],
    ]);

    $activities = collect([
        (new Activity)->forceFill(['id' => 10, 'type' => ActivityType::Call, 'subject' => 'Demo']),
    ]);

    $result = makeOllamaService()->smartSearch('find Ada', $contacts, $activities);

    expect($result)->toBe('interpretation');
    Http::assertSent(function ($request) {
        return str_contains($request['prompt'], 'find Ada')
            && str_contains($request['prompt'], 'ID:1 Ada Lovelace (ada@example.com) at Acme')
            && str_contains($request['prompt'], 'ID:2 Bob Smith (no email) at N/A')
            && str_contains($request['prompt'], 'ID:10 [Call] Demo');
    });
});

it('returns the unavailable message when the API returns a non-success status', function () {
    Http::fake([
        '*/api/generate' => Http::response([], 500),
    ]);
    Log::spy();

    $result = makeOllamaService()->summarize('hello');

    expect($result)->toBe('AI service is currently unavailable. Please try again later.');
    Log::shouldHaveReceived('error')->once();
});

it('returns the unavailable message when the HTTP call throws', function () {
    Http::fake(function () {
        throw new RuntimeException('boom');
    });
    Log::spy();

    $result = makeOllamaService()->summarize('hello');

    expect($result)->toBe('AI service is currently unavailable. Please try again later.');
    Log::shouldHaveReceived('error')->once();
});

it('returns an empty string when the response key is missing', function () {
    Http::fake([
        '*/api/generate' => Http::response(['unexpected' => 'shape'], 200),
    ]);

    expect(makeOllamaService()->summarize('hi'))->toBe('');
});
