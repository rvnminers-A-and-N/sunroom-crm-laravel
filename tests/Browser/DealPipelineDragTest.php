<?php

use App\Enums\DealStage;
use App\Models\Deal;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\DealPipeline;

it('renders deals in the columns matching their stage', function () {
    $user = User::factory()->create();
    $leadDeal = Deal::factory()->for($user)->create([
        'title' => 'Sun Atrium Build',
        'value' => 8500,
        'stage' => DealStage::Lead,
    ]);
    $negotiationDeal = Deal::factory()->for($user)->create([
        'title' => 'Glass Roof Pavilion',
        'value' => 14250,
        'stage' => DealStage::Negotiation,
    ]);

    $this->browse(function (Browser $browser) use ($user, $leadDeal, $negotiationDeal) {
        $page = new DealPipeline;

        $browser->loginAs($user)
            ->visit($page)
            ->waitForText('Sun Atrium Build')
            ->within($page->stageColumn('Lead'), function (Browser $column) use ($leadDeal) {
                $column->assertSee('Sun Atrium Build')
                    ->assertPresent("[data-deal-id=\"{$leadDeal->id}\"]");
            })
            ->within($page->stageColumn('Negotiation'), function (Browser $column) use ($negotiationDeal) {
                $column->assertSee('Glass Roof Pavilion')
                    ->assertPresent("[data-deal-id=\"{$negotiationDeal->id}\"]");
            });
    });
});

it('moves a deal between stages via the Livewire updateStage round-trip', function () {
    $user = User::factory()->create();
    $deal = Deal::factory()->for($user)->create([
        'title' => 'Sliding Sun Roof',
        'value' => 12000,
        'stage' => DealStage::Lead,
        'closed_at' => null,
    ]);

    $this->browse(function (Browser $browser) use ($user, $deal) {
        $browser->loginAs($user)
            ->visit(new DealPipeline)
            ->waitForText('Sliding Sun Roof')
            ->script("Livewire.getByName('deals.deal-pipeline')[0].call('updateStage', {$deal->id}, 'Qualified');");

        $browser->waitUsing(5, 100, function () use ($deal) {
            return $deal->fresh()->stage === DealStage::Qualified;
        });
    });

    expect($deal->fresh()->stage)->toBe(DealStage::Qualified)
        ->and($deal->fresh()->closed_at)->toBeNull();
});

it('sets closed_at when a deal moves into Won via the pipeline', function () {
    $user = User::factory()->create();
    $deal = Deal::factory()->for($user)->create([
        'title' => 'Conservatory Glaze',
        'value' => 9999,
        'stage' => DealStage::Negotiation,
        'closed_at' => null,
    ]);

    $this->browse(function (Browser $browser) use ($user, $deal) {
        $browser->loginAs($user)
            ->visit(new DealPipeline)
            ->waitForText('Conservatory Glaze')
            ->script("Livewire.getByName('deals.deal-pipeline')[0].call('updateStage', {$deal->id}, 'Won');");

        $browser->waitUsing(5, 100, function () use ($deal) {
            return $deal->fresh()->closed_at !== null;
        });
    });

    expect($deal->fresh()->stage)->toBe(DealStage::Won)
        ->and($deal->fresh()->closed_at)->not->toBeNull();
});

it('clears closed_at when a Won deal moves back to an open stage', function () {
    $user = User::factory()->create();
    $deal = Deal::factory()->for($user)->create([
        'title' => 'Recovery Deal',
        'value' => 4321,
        'stage' => DealStage::Won,
        'closed_at' => now(),
    ]);

    $this->browse(function (Browser $browser) use ($user, $deal) {
        $browser->loginAs($user)
            ->visit(new DealPipeline)
            ->waitForText('Recovery Deal')
            ->script("Livewire.getByName('deals.deal-pipeline')[0].call('updateStage', {$deal->id}, 'Proposal');");

        $browser->waitUsing(5, 100, function () use ($deal) {
            return $deal->fresh()->stage === DealStage::Proposal;
        });
    });

    expect($deal->fresh()->closed_at)->toBeNull();
});
