<?php

use App\Enums\DealStage;

it('exposes every deal stage case with the right backing value', function () {
    expect(DealStage::Lead->value)->toBe('Lead')
        ->and(DealStage::Qualified->value)->toBe('Qualified')
        ->and(DealStage::Proposal->value)->toBe('Proposal')
        ->and(DealStage::Negotiation->value)->toBe('Negotiation')
        ->and(DealStage::Won->value)->toBe('Won')
        ->and(DealStage::Lost->value)->toBe('Lost');
});

it('lists exactly the six known stages in pipeline order', function () {
    expect(DealStage::cases())->toHaveCount(6)
        ->and(array_map(fn (DealStage $s) => $s->value, DealStage::cases()))
        ->toBe(['Lead', 'Qualified', 'Proposal', 'Negotiation', 'Won', 'Lost']);
});

it('round-trips through the from() helper', function () {
    foreach (['Lead', 'Qualified', 'Proposal', 'Negotiation', 'Won', 'Lost'] as $value) {
        expect(DealStage::from($value)->value)->toBe($value);
    }
});

it('treats Won and Lost as the closing stages', function () {
    $closing = array_filter(
        DealStage::cases(),
        fn (DealStage $s) => in_array($s, [DealStage::Won, DealStage::Lost], true),
    );

    expect($closing)->toHaveCount(2);
});
