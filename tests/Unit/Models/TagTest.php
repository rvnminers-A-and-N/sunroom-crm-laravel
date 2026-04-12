<?php

use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

it('exposes contacts as a BelongsToMany relation', function () {
    $tag = Tag::factory()->create();
    $contacts = Contact::factory()->count(3)->create();
    $tag->contacts()->attach($contacts->pluck('id'));

    expect($tag->contacts())->toBeInstanceOf(BelongsToMany::class)
        ->and($tag->fresh()->contacts)->toHaveCount(3);
});

it('disables the updated_at timestamp', function () {
    expect(Tag::UPDATED_AT)->toBeNull();

    $tag = Tag::create(['name' => 'Hot Lead', 'color' => '#ff0000']);

    expect($tag->updated_at)->toBeNull()
        ->and($tag->created_at)->not->toBeNull();
});
