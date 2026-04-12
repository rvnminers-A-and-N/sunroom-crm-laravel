<?php

use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

it('serializes a tag with id, name, color, and createdAt', function () {
    $tag = Tag::factory()->create([
        'name' => 'Hot Lead',
        'color' => '#ff0000',
    ]);

    $array = (new TagResource($tag))->toArray(Request::create('/'));

    expect($array)
        ->toHaveKeys(['id', 'name', 'color', 'createdAt'])
        ->and($array['id'])->toBe($tag->id)
        ->and($array['name'])->toBe('Hot Lead')
        ->and($array['color'])->toBe('#ff0000')
        ->and($array['createdAt'])->toBeString();
});
