<?php

use App\Enums\DealStage;
use App\Livewire\Deals\DealPipeline;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

it('redirects to login when not authenticated', function () {
    $this->get(route('deals.pipeline'))->assertRedirect('/login');
});

it('renders the pipeline grouped by stage with only the user\'s deals', function () {
    $me = actingAsUser();
    $other = User::factory()->create();
    $contact = Contact::factory()->for($me)->create();
    Deal::factory()->for($me)->for($contact)->create(['title' => 'MyLead', 'stage' => DealStage::Lead]);
    Deal::factory()->for($me)->for($contact)->create(['title' => 'MyWon', 'stage' => DealStage::Won, 'closed_at' => now()]);
    $otherContact = Contact::factory()->for($other)->create();
    Deal::factory()->for($other)->for($otherContact)->create(['title' => 'TheirLead', 'stage' => DealStage::Lead]);

    Livewire::test(DealPipeline::class)
        ->assertStatus(200)
        ->assertSee('MyLead')
        ->assertSee('MyWon')
        ->assertDontSee('TheirLead');
});

it('moves a deal to a non-terminal stage and clears closed_at', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();
    $deal = Deal::factory()->for($me)->for($contact)->create([
        'stage' => DealStage::Won,
        'closed_at' => now()->subDay(),
    ]);

    Livewire::test(DealPipeline::class)
        ->call('updateStage', $deal->id, 'Negotiation');

    $fresh = $deal->fresh();
    expect($fresh->stage)->toBe(DealStage::Negotiation)
        ->and($fresh->closed_at)->toBeNull();
});

it('moves a deal to Won and sets closed_at', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();
    $deal = Deal::factory()->for($me)->for($contact)->create([
        'stage' => DealStage::Proposal,
        'closed_at' => null,
    ]);

    Livewire::test(DealPipeline::class)
        ->call('updateStage', $deal->id, 'Won');

    $fresh = $deal->fresh();
    expect($fresh->stage)->toBe(DealStage::Won)
        ->and($fresh->closed_at)->not->toBeNull();
});

it('moves a deal to Lost and sets closed_at', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();
    $deal = Deal::factory()->for($me)->for($contact)->create([
        'stage' => DealStage::Qualified,
        'closed_at' => null,
    ]);

    Livewire::test(DealPipeline::class)
        ->call('updateStage', $deal->id, 'Lost');

    $fresh = $deal->fresh();
    expect($fresh->stage)->toBe(DealStage::Lost)
        ->and($fresh->closed_at)->not->toBeNull();
});

it('forbids moving a deal owned by another user', function () {
    actingAsUser();
    $other = User::factory()->create();
    $contact = Contact::factory()->for($other)->create();
    $deal = Deal::factory()->for($other)->for($contact)->create(['stage' => DealStage::Lead]);

    Livewire::test(DealPipeline::class)
        ->call('updateStage', $deal->id, 'Won')
        ->assertStatus(403);

    expect($deal->fresh()->stage)->toBe(DealStage::Lead);
});

it('throws ModelNotFoundException for a non-existent deal id', function () {
    actingAsUser();

    Livewire::test(DealPipeline::class)
        ->call('updateStage', 999999, 'Won');
})->throws(ModelNotFoundException::class);
