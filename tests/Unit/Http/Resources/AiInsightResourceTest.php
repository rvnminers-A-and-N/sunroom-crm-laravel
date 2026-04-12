<?php

use App\Http\Resources\AiInsightResource;
use App\Models\AiInsight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

it('serializes an AI insight with id, insight text, and generatedAt', function () {
    $insight = AiInsight::factory()->create([
        'insight' => 'This deal is at risk.',
        'generated_at' => '2026-03-01 12:00:00',
    ]);

    $array = (new AiInsightResource($insight))->toArray(Request::create('/'));

    expect($array)
        ->toHaveKeys(['id', 'insight', 'generatedAt'])
        ->and($array['id'])->toBe($insight->id)
        ->and($array['insight'])->toBe('This deal is at risk.')
        ->and($array['generatedAt'])->toBeString();
});

it('serializes an insight with a null generatedAt', function () {
    $insight = AiInsight::factory()->make(['generated_at' => null]);

    $array = (new AiInsightResource($insight))->toArray(Request::create('/'));

    expect($array['generatedAt'])->toBeNull();
});
