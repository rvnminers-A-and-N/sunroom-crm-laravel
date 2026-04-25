<?php

use App\Livewire\AiAssistant;
use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use Livewire\Livewire;

it('redirects to login when not authenticated', function () {
    $this->get(route('ai.index'))->assertRedirect('/login');
});

it('renders the assistant for an authenticated user', function () {
    actingAsUser();
    fakeOllama();

    Livewire::test(AiAssistant::class)
        ->assertStatus(200)
        ->assertSet('messages', [])
        ->assertSet('loading', false);
});

it('prepareAsk builds context and sets loading state', function () {
    $me = actingAsUser();
    $company = Company::factory()->for($me)->create(['name' => 'Acme']);
    Contact::factory()->for($me)->for($company)->create(['first_name' => 'Ada', 'last_name' => 'Lovelace']);
    Contact::factory()->for($me)->create(['first_name' => 'Bob', 'last_name' => 'No Co']);
    Activity::factory()->for($me)->create(['subject' => 'Recent thing']);
    fakeOllama();

    $component = Livewire::test(AiAssistant::class)
        ->set('question', 'What should I do next?')
        ->call('prepareAsk');

    $component->assertSet('question', '')
        ->assertSet('loading', true)
        ->assertHasNoErrors();

    $messages = $component->get('messages');
    expect($messages)->toHaveCount(1)
        ->and($messages[0])->toBe(['role' => 'user', 'content' => 'What should I do next?']);
});

it('finishAsk stores the answer and clears loading', function () {
    actingAsUser();
    fakeOllama();

    $component = Livewire::test(AiAssistant::class)
        ->set('messages', [['role' => 'user', 'content' => 'Hello']])
        ->set('loading', true)
        ->call('finishAsk', 'Here is my answer.');

    $component->assertSet('loading', false);

    $messages = $component->get('messages');
    expect($messages)->toHaveCount(2)
        ->and($messages[1])->toBe(['role' => 'assistant', 'content' => 'Here is my answer.']);
});

it('validates that question is required', function () {
    actingAsUser();
    fakeOllama();

    Livewire::test(AiAssistant::class)
        ->set('question', '')
        ->call('prepareAsk')
        ->assertHasErrors(['question' => 'required']);
});

it('validates the question length cap of 500 characters', function () {
    actingAsUser();
    fakeOllama();

    Livewire::test(AiAssistant::class)
        ->set('question', str_repeat('a', 501))
        ->call('prepareAsk')
        ->assertHasErrors(['question' => 'max']);
});

it('clears the chat history', function () {
    actingAsUser();
    fakeOllama();

    Livewire::test(AiAssistant::class)
        ->set('messages', [['role' => 'user', 'content' => 'old']])
        ->call('clearChat')
        ->assertSet('messages', []);
});

it('prepareSearch validates and sets loading state', function () {
    actingAsUser();
    fakeOllama();

    $component = Livewire::test(AiAssistant::class)
        ->set('searchQuery', 'find VIPs')
        ->call('prepareSearch');

    $component->assertSet('searchLoading', true)
        ->assertSet('searchResult', '')
        ->assertHasNoErrors();
});

it('prepareSearch validates that query is required', function () {
    actingAsUser();
    fakeOllama();

    Livewire::test(AiAssistant::class)
        ->set('searchQuery', '')
        ->call('prepareSearch')
        ->assertHasErrors(['searchQuery' => 'required']);
});

it('finishSearch stores result and clears loading', function () {
    actingAsUser();
    fakeOllama();

    $component = Livewire::test(AiAssistant::class)
        ->set('searchLoading', true)
        ->call('finishSearch', 'Found 3 matching contacts.');

    $component->assertSet('searchResult', 'Found 3 matching contacts.')
        ->assertSet('searchLoading', false);
});

it('prepareDealInsights validates and sets loading state', function () {
    $me = actingAsUser();
    $deal = Deal::factory()->for($me)->create();
    fakeOllama();

    $component = Livewire::test(AiAssistant::class)
        ->set('insightsDealId', $deal->id)
        ->call('prepareDealInsights');

    $component->assertSet('insightsLoading', true)
        ->assertSet('insightsResult', '')
        ->assertHasNoErrors();
});

it('prepareDealInsights validates deal id is required', function () {
    actingAsUser();
    fakeOllama();

    Livewire::test(AiAssistant::class)
        ->set('insightsDealId', '')
        ->call('prepareDealInsights')
        ->assertHasErrors(['insightsDealId' => 'required']);
});

it('finishDealInsights stores result and clears loading', function () {
    actingAsUser();
    fakeOllama();

    $component = Livewire::test(AiAssistant::class)
        ->set('insightsLoading', true)
        ->call('finishDealInsights', 'Deal shows strong momentum.');

    $component->assertSet('insightsResult', 'Deal shows strong momentum.')
        ->assertSet('insightsLoading', false);
});
