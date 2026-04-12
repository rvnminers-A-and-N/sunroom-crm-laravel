<?php

use App\Livewire\AiAssistant;
use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use Illuminate\Support\Facades\Http;
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

it('asks the assistant a question and stores the message exchange', function () {
    $me = actingAsUser();
    $company = Company::factory()->for($me)->create(['name' => 'Acme']);
    Contact::factory()->for($me)->for($company)->create(['first_name' => 'Ada', 'last_name' => 'Lovelace']);
    Contact::factory()->for($me)->create(['first_name' => 'Bob', 'last_name' => 'No Co']);
    Activity::factory()->for($me)->create(['subject' => 'Recent thing']);
    fakeOllama(['response' => 'Here is my answer.']);

    $component = Livewire::test(AiAssistant::class)
        ->set('question', 'What should I do next?')
        ->call('ask')
        ->assertSet('question', '')
        ->assertSet('loading', false)
        ->assertHasNoErrors();

    $messages = $component->get('messages');
    expect($messages)->toHaveCount(2)
        ->and($messages[0])->toBe(['role' => 'user', 'content' => 'What should I do next?'])
        ->and($messages[1])->toBe(['role' => 'assistant', 'content' => 'Here is my answer.']);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/api/generate')
            && str_contains($request['prompt'], 'What should I do next?')
            && str_contains($request['prompt'], 'Ada Lovelace at Acme')
            && str_contains($request['prompt'], 'Bob No Co')
            && str_contains($request['prompt'], 'Recent thing');
    });
});

it('validates that question is required', function () {
    actingAsUser();
    fakeOllama();

    Livewire::test(AiAssistant::class)
        ->set('question', '')
        ->call('ask')
        ->assertHasErrors(['question' => 'required']);
});

it('validates the question length cap of 500 characters', function () {
    actingAsUser();
    fakeOllama();

    Livewire::test(AiAssistant::class)
        ->set('question', str_repeat('a', 501))
        ->call('ask')
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
