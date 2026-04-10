<?php

namespace App\Http\Controllers\Api;

use App\Enums\ActivityType;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActivityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('perPage', 25);

        $activities = Activity::where('user_id', $request->user()->id)
            ->with(['contact', 'deal', 'user'])
            ->when($request->query('search'), fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('subject', 'ilike', "%{$search}%")
                  ->orWhere('body', 'ilike', "%{$search}%");
            }))
            ->when($request->query('type'), fn ($q, $type) => $q->where('type', $type))
            ->when($request->query('contactId'), fn ($q, $id) => $q->where('contact_id', $id))
            ->when($request->query('dealId'), fn ($q, $id) => $q->where('deal_id', $id))
            ->orderByDesc('occurred_at')
            ->paginate($perPage);

        return ActivityResource::collection($activities);
    }

    public function show(Request $request, int $id): ActivityResource
    {
        $activity = Activity::with(['contact', 'deal', 'user'])->findOrFail($id);
        $this->authorize('view', $activity);

        return new ActivityResource($activity);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'subject' => 'required|string|max:255',
            'body' => 'nullable|string',
            'occurredAt' => 'required|date',
            'contactId' => 'nullable|exists:contacts,id',
            'dealId' => 'nullable|exists:deals,id',
        ]);

        $activity = Activity::create([
            'user_id' => $request->user()->id,
            'type' => ActivityType::from($validated['type']),
            'subject' => $validated['subject'],
            'body' => $validated['body'] ?? null,
            'occurred_at' => $validated['occurredAt'],
            'contact_id' => $validated['contactId'] ?? null,
            'deal_id' => $validated['dealId'] ?? null,
        ]);

        $activity->load(['contact', 'deal', 'user']);

        return (new ActivityResource($activity))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $id): ActivityResource
    {
        $activity = Activity::findOrFail($id);
        $this->authorize('update', $activity);

        $validated = $request->validate([
            'type' => 'required|string',
            'subject' => 'required|string|max:255',
            'body' => 'nullable|string',
            'occurredAt' => 'required|date',
            'contactId' => 'nullable|exists:contacts,id',
            'dealId' => 'nullable|exists:deals,id',
        ]);

        $activity->update([
            'type' => ActivityType::from($validated['type']),
            'subject' => $validated['subject'],
            'body' => $validated['body'] ?? null,
            'occurred_at' => $validated['occurredAt'],
            'contact_id' => $validated['contactId'] ?? null,
            'deal_id' => $validated['dealId'] ?? null,
        ]);

        $activity->load(['contact', 'deal', 'user']);

        return new ActivityResource($activity);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $activity = Activity::findOrFail($id);
        $this->authorize('delete', $activity);
        $activity->delete();

        return response()->json(null, 204);
    }
}
