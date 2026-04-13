<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\ContactResource;
use App\Http\Resources\DealResource;
use App\Http\Resources\TagResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('perPage', 25);

        $contacts = Contact::where('user_id', $request->user()->id)
            ->with(['company', 'tags'])
            ->when($request->query('search'), fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('first_name', 'ilike', "%{$search}%")
                    ->orWhere('last_name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            }))
            ->when($request->query('companyId'), fn ($q, $id) => $q->where('company_id', $id))
            ->when($request->query('tagId'), fn ($q, $id) => $q->whereHas('tags', fn ($t) => $t->where('tags.id', $id)))
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return ContactResource::collection($contacts);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $contact = Contact::with(['company', 'tags', 'deals.company', 'activities.user'])->findOrFail($id);
        $this->authorize('view', $contact);

        return response()->json([
            'id' => $contact->id,
            'firstName' => $contact->first_name,
            'lastName' => $contact->last_name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'title' => $contact->title,
            'notes' => $contact->notes,
            'lastContactedAt' => $contact->last_contacted_at?->toIso8601String(),
            'createdAt' => $contact->created_at?->toIso8601String(),
            'updatedAt' => $contact->updated_at?->toIso8601String(),
            'company' => $contact->company ? [
                'id' => $contact->company->id,
                'name' => $contact->company->name,
                'industry' => $contact->company->industry,
                'city' => $contact->company->city,
                'state' => $contact->company->state,
            ] : null,
            'tags' => TagResource::collection($contact->tags),
            'deals' => DealResource::collection($contact->deals),
            'activities' => ActivityResource::collection($contact->activities->sortByDesc('occurred_at')->values()),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'firstName' => 'required|string|max:100',
            'lastName' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'companyId' => 'nullable|exists:companies,id',
            'tagIds' => 'nullable|array',
            'tagIds.*' => 'exists:tags,id',
        ]);

        $contact = Contact::create([
            'user_id' => $request->user()->id,
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'title' => $validated['title'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'company_id' => $validated['companyId'] ?? null,
        ]);

        if (! empty($validated['tagIds'])) {
            $contact->tags()->sync($validated['tagIds']);
        }

        $contact->load(['company', 'tags']);

        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $id): ContactResource
    {
        $contact = Contact::findOrFail($id);
        $this->authorize('update', $contact);

        $validated = $request->validate([
            'firstName' => 'required|string|max:100',
            'lastName' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'companyId' => 'nullable|exists:companies,id',
        ]);

        $contact->update([
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'title' => $validated['title'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'company_id' => $validated['companyId'] ?? null,
        ]);

        $contact->load(['company', 'tags']);

        return new ContactResource($contact);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $contact = Contact::findOrFail($id);
        $this->authorize('delete', $contact);
        $contact->delete();

        return response()->json(null, 204);
    }

    public function syncTags(Request $request, int $id): ContactResource
    {
        $contact = Contact::findOrFail($id);
        $this->authorize('update', $contact);

        $validated = $request->validate([
            'tagIds' => 'required|array',
            'tagIds.*' => 'exists:tags,id',
        ]);

        $contact->tags()->sync($validated['tagIds']);
        $contact->load(['company', 'tags']);

        return new ContactResource($contact);
    }
}
