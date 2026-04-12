<?php

use App\Models\AiInsight;
use App\Models\Deal;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

it('declares the expected casts', function () {
    expect((new AiInsight)->getCasts())
        ->toMatchArray(['generated_at' => 'datetime']);
});

it('casts generated_at to a Carbon datetime', function () {
    $insight = AiInsight::factory()->create(['generated_at' => '2026-03-01 12:00:00']);

    expect($insight->generated_at)->toBeInstanceOf(Carbon::class)
        ->and($insight->generated_at->toDateTimeString())->toBe('2026-03-01 12:00:00');
});

it('belongs to a deal', function () {
    $deal = Deal::factory()->create();
    $insight = AiInsight::factory()->for($deal)->create();

    expect($insight->deal())->toBeInstanceOf(BelongsTo::class)
        ->and($insight->deal)->toBeInstanceOf(Deal::class)
        ->and($insight->deal->id)->toBe($deal->id);
});

it('disables the updated_at timestamp', function () {
    expect(AiInsight::UPDATED_AT)->toBeNull();

    $insight = AiInsight::factory()->create();

    expect($insight->updated_at)->toBeNull()
        ->and($insight->created_at)->not->toBeNull();
});
