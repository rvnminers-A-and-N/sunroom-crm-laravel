<?php

use App\Enums\ActivityType;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

it('serializes an activity with all relations populated', function () {
    $user = User::factory()->create(['name' => 'Alice']);
    $contact = Contact::factory()->create([
        'first_name' => 'Bob',
        'last_name' => 'Builder',
    ]);
    $deal = Deal::factory()->create(['title' => 'Construction Deal']);

    $activity = Activity::factory()
        ->for($user)
        ->for($contact)
        ->for($deal)
        ->create([
            'type' => ActivityType::Call,
            'subject' => 'Initial discovery call',
            'body' => 'Discussed scope.',
            'occurred_at' => '2026-02-15 10:00:00',
            'ai_summary' => 'Productive call.',
        ]);
    $activity->load(['user', 'contact', 'deal']);

    $array = (new ActivityResource($activity))->toArray(Request::create('/'));

    expect($array)
        ->toHaveKeys([
            'id', 'type', 'subject', 'body', 'aiSummary',
            'contactId', 'contactName', 'dealId', 'dealTitle',
            'userName', 'occurredAt', 'createdAt',
        ])
        ->and($array['type'])->toBe('Call')
        ->and($array['subject'])->toBe('Initial discovery call')
        ->and($array['body'])->toBe('Discussed scope.')
        ->and($array['aiSummary'])->toBe('Productive call.')
        ->and($array['contactName'])->toBe('Bob Builder')
        ->and($array['dealTitle'])->toBe('Construction Deal')
        ->and($array['userName'])->toBe('Alice')
        ->and($array['occurredAt'])->toBeString();
});

it('serializes an activity with no contact, no deal, and no user', function () {
    $activity = Activity::factory()->create();
    $activity->setRelation('contact', null);
    $activity->setRelation('deal', null);
    $activity->setRelation('user', null);

    $array = (new ActivityResource($activity))->toArray(Request::create('/'));

    expect($array['contactName'])->toBeNull()
        ->and($array['dealTitle'])->toBeNull()
        ->and($array['userName'])->toBe('');
});
