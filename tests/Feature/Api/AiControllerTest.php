<?php

use App\Models\Activity;
use App\Models\AiInsight;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

it('rejects summarize when unauthenticated', function () {
    $this->postJson('/api/ai/summarize', ['text' => 'hi'])->assertStatus(401);
});

it('summarizes text via OllamaService', function () {
    Sanctum::actingAs(User::factory()->create());
    fakeOllama(['response' => 'short summary']);

    $response = $this->postJson('/api/ai/summarize', [
        'text' => 'Long activity notes that need a summary.',
    ]);

    $response->assertOk()->assertJson(['summary' => 'short summary']);
    Http::assertSent(fn ($request) => str_contains($request->url(), '/api/generate'));
});

it('rejects summarize with missing text', function () {
    Sanctum::actingAs(User::factory()->create());
    fakeOllama();

    $this->postJson('/api/ai/summarize', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors('text');
});

it('rejects dealInsights when unauthenticated', function () {
    fakeOllama();
    $deal = Deal::factory()->create();

    $this->postJson("/api/ai/deal-insights/{$deal->id}")->assertStatus(401);
});

it('generates an insight for a deal owned by the authenticated user', function () {
    $me = User::factory()->create();
    $deal = Deal::factory()->for($me)->create();
    Activity::factory()->for($me)->state(['deal_id' => $deal->id])->count(2)->create();
    Sanctum::actingAs($me);
    fakeOllama(['response' => 'Try these next steps...']);

    $response = $this->postJson("/api/ai/deal-insights/{$deal->id}");

    $response->assertCreated()
        ->assertJsonPath('insight', 'Try these next steps...')
        ->assertJsonStructure(['id', 'insight', 'generatedAt']);

    expect(AiInsight::where('deal_id', $deal->id)->count())->toBe(1);
});

it('returns 404 when generating insights for a non-existent deal', function () {
    Sanctum::actingAs(User::factory()->create());
    fakeOllama();

    $this->postJson('/api/ai/deal-insights/999999')->assertStatus(404);
});

it('forbids dealInsights when the deal belongs to another user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $deal = Deal::factory()->for($owner)->create();
    Sanctum::actingAs($other);
    fakeOllama();

    $this->postJson("/api/ai/deal-insights/{$deal->id}")->assertStatus(403);
});

it('rejects search when unauthenticated', function () {
    fakeOllama();

    $this->postJson('/api/ai/search', ['query' => 'find'])->assertStatus(401);
});

it('runs a smart search and returns the interpretation', function () {
    $me = User::factory()->create();
    Contact::factory()->for($me)->count(2)->create();
    Activity::factory()->for($me)->count(2)->create();
    Sanctum::actingAs($me);
    fakeOllama(['response' => 'You are looking for VIPs in NYC.']);

    $response = $this->postJson('/api/ai/search', ['query' => 'VIPs in NYC']);

    $response->assertOk()->assertJson(['interpretation' => 'You are looking for VIPs in NYC.']);
});

it('rejects search with missing query', function () {
    Sanctum::actingAs(User::factory()->create());
    fakeOllama();

    $this->postJson('/api/ai/search', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors('query');
});

it('rejects search with a query longer than 500 characters', function () {
    Sanctum::actingAs(User::factory()->create());
    fakeOllama();

    $this->postJson('/api/ai/search', ['query' => str_repeat('a', 501)])
        ->assertStatus(422)
        ->assertJsonValidationErrors('query');
});
