<?php

use App\Models\Tag;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('rejects index when unauthenticated', function () {
    $this->getJson('/api/tags')->assertStatus(401);
});

it('lists every tag ordered by name for any authenticated user', function () {
    Tag::factory()->create(['name' => 'Charlie']);
    Tag::factory()->create(['name' => 'Alpha']);
    Tag::factory()->create(['name' => 'Bravo']);
    Sanctum::actingAs(User::factory()->create());

    $names = collect($this->getJson('/api/tags')->json('data'))->pluck('name')->all();

    expect($names)->toBe(['Alpha', 'Bravo', 'Charlie']);
});

it('creates a tag with name and color', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/tags', [
        'name' => 'Hot Lead',
        'color' => '#ff0000',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Hot Lead')
        ->assertJsonPath('data.color', '#ff0000');

    expect(Tag::where('name', 'Hot Lead')->exists())->toBeTrue();
});

it('rejects store with missing fields', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/tags', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'color']);
});

it('rejects store when name is already taken', function () {
    Tag::factory()->create(['name' => 'Hot Lead']);
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/tags', ['name' => 'Hot Lead', 'color' => '#ff0000'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

it('rejects store with a color longer than 7 characters', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/tags', ['name' => 'TooLong', 'color' => '#ffffffff'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('color');
});

it('updates a tag', function () {
    $tag = Tag::factory()->create(['name' => 'Old', 'color' => '#000000']);
    Sanctum::actingAs(User::factory()->create());

    $response = $this->putJson("/api/tags/{$tag->id}", [
        'name' => 'New',
        'color' => '#ffffff',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'New')
        ->assertJsonPath('data.color', '#ffffff');
});

it('allows updating a tag while keeping the same name', function () {
    $tag = Tag::factory()->create(['name' => 'Stable', 'color' => '#000000']);
    Sanctum::actingAs(User::factory()->create());

    $this->putJson("/api/tags/{$tag->id}", [
        'name' => 'Stable',
        'color' => '#ffffff',
    ])->assertOk();
});

it('rejects update with missing fields', function () {
    $tag = Tag::factory()->create();
    Sanctum::actingAs(User::factory()->create());

    $this->putJson("/api/tags/{$tag->id}", [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'color']);
});

it('rejects update when name conflicts with another tag', function () {
    $existing = Tag::factory()->create(['name' => 'Taken']);
    $target = Tag::factory()->create(['name' => 'Free']);
    Sanctum::actingAs(User::factory()->create());

    $this->putJson("/api/tags/{$target->id}", [
        'name' => 'Taken',
        'color' => '#000000',
    ])->assertStatus(422)->assertJsonValidationErrors('name');
});

it('returns 404 when updating a non-existent tag', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->putJson('/api/tags/999999', ['name' => 'Ghost', 'color' => '#000000'])->assertStatus(404);
});

it('deletes a tag', function () {
    $tag = Tag::factory()->create();
    Sanctum::actingAs(User::factory()->create());

    $this->deleteJson("/api/tags/{$tag->id}")->assertNoContent();
    expect(Tag::find($tag->id))->toBeNull();
});

it('returns 404 when deleting a non-existent tag', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->deleteJson('/api/tags/999999')->assertStatus(404);
});
