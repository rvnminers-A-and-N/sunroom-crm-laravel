<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TagController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TagResource::collection(Tag::orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name',
            'color' => 'required|string|max:7',
        ]);

        $tag = Tag::create($validated);

        return (new TagResource($tag))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $id): TagResource
    {
        $tag = Tag::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name,'.$id,
            'color' => 'required|string|max:7',
        ]);

        $tag->update($validated);

        return new TagResource($tag);
    }

    public function destroy(int $id): JsonResponse
    {
        $tag = Tag::findOrFail($id);
        $tag->delete();

        return response()->json(null, 204);
    }
}
