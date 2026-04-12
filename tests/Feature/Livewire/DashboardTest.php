<?php

use App\Enums\DealStage;
use App\Livewire\Dashboard;
use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Livewire\Livewire;

it('redirects to login when not authenticated', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('renders the dashboard with totals scoped to the user', function () {
    $me = actingAsUser(['name' => 'Me']);
    $other = User::factory()->create();

    Contact::factory()->for($me)->count(3)->create();
    Contact::factory()->for($other)->count(5)->create();
    Company::factory()->for($me)->count(2)->create();
    Company::factory()->for($other)->count(4)->create();

    $contact = Contact::factory()->for($me)->create();
    Deal::factory()->for($me)->for($contact)->create(['stage' => DealStage::Lead, 'value' => 1000]);
    Deal::factory()->for($me)->for($contact)->create(['stage' => DealStage::Lead, 'value' => 500]);
    Deal::factory()->for($me)->for($contact)->create(['stage' => DealStage::Won, 'value' => 9000, 'closed_at' => now()]);
    Deal::factory()->for($me)->for($contact)->create(['stage' => DealStage::Lost, 'value' => 100, 'closed_at' => now()]);
    Deal::factory()->for($other)->state(['stage' => DealStage::Lead, 'value' => 999999])->create();

    Activity::factory()->for($me)->for($contact)->create(['subject' => 'Recent activity', 'occurred_at' => now()]);

    $component = Livewire::test(Dashboard::class)
        ->assertStatus(200)
        ->assertSee('Recent activity');

    expect($component->viewData('totalContacts'))->toBe(4)
        ->and($component->viewData('totalCompanies'))->toBe(2)
        ->and($component->viewData('totalDeals'))->toBe(4)
        ->and((float) $component->viewData('pipelineValue'))->toBe(1500.0)
        ->and((float) $component->viewData('wonRevenue'))->toBe(9000.0);
});

it('returns zero values for a fresh user with no data', function () {
    actingAsUser();

    $component = Livewire::test(Dashboard::class)->assertStatus(200);

    expect($component->viewData('totalContacts'))->toBe(0)
        ->and($component->viewData('totalCompanies'))->toBe(0)
        ->and($component->viewData('totalDeals'))->toBe(0)
        ->and((float) $component->viewData('pipelineValue'))->toBe(0.0)
        ->and((float) $component->viewData('wonRevenue'))->toBe(0.0);
});

it('limits recentActivities to the 10 most recent for the user', function () {
    $me = actingAsUser();
    foreach (range(1, 12) as $i) {
        Activity::factory()->for($me)->create([
            'subject' => "Activity {$i}",
            'occurred_at' => now()->subDays(20 - $i),
        ]);
    }

    $component = Livewire::test(Dashboard::class);
    $recent = $component->viewData('recentActivities');
    expect($recent)->toHaveCount(10)
        ->and($recent->first()->subject)->toBe('Activity 12')
        ->and($recent->last()->subject)->toBe('Activity 3');
});

it('exposes deals grouped by stage in the view data', function () {
    $me = actingAsUser();
    $contact = Contact::factory()->for($me)->create();
    Deal::factory()->for($me)->for($contact)->create(['stage' => DealStage::Proposal, 'value' => 200]);
    Deal::factory()->for($me)->for($contact)->create(['stage' => DealStage::Proposal, 'value' => 300]);

    $component = Livewire::test(Dashboard::class);
    $byStage = $component->viewData('dealsByStage');

    expect($byStage->has('Proposal'))->toBeTrue()
        ->and((int) $byStage['Proposal']->count)->toBe(2)
        ->and((float) $byStage['Proposal']->total_value)->toBe(500.0);
});
