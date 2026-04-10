<?php

namespace App\Http\Controllers\Api;

use App\Enums\DealStage;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\AiInsightResource;
use App\Http\Resources\DealResource;
use App\Models\Deal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DealController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('perPage', 25);

        $deals = Deal::where('user_id', $request->user()->id)
            ->with(['contact', 'company'])
            ->when($request->query('search'), fn ($q, $search) => $q->where('title', 'ilike', "%{$search}%"))
            ->when($request->query('stage'), fn ($q, $stage) => $q->where('stage', $stage))
            ->when($request->query('contactId'), fn ($q, $id) => $q->where('contact_id', $id))
            ->when($request->query('companyId'), fn ($q, $id) => $q->where('company_id', $id))
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return DealResource::collection($deals);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $deal = Deal::with(['contact', 'company', 'activities.user', 'aiInsights'])->findOrFail($id);
        $this->authorize('view', $deal);

        return response()->json([
            'id' => $deal->id,
            'title' => $deal->title,
            'value' => (float) $deal->value,
            'stage' => $deal->stage->value,
            'contactId' => $deal->contact_id,
            'contactName' => $deal->contact ? trim("{$deal->contact->first_name} {$deal->contact->last_name}") : '',
            'companyId' => $deal->company_id,
            'companyName' => $deal->company?->name,
            'expectedCloseDate' => $deal->expected_close_date?->toIso8601String(),
            'closedAt' => $deal->closed_at?->toIso8601String(),
            'notes' => $deal->notes,
            'createdAt' => $deal->created_at?->toIso8601String(),
            'updatedAt' => $deal->updated_at?->toIso8601String(),
            'activities' => ActivityResource::collection($deal->activities->sortByDesc('occurred_at')->values()),
            'insights' => AiInsightResource::collection($deal->aiInsights->sortByDesc('generated_at')->values()),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'stage' => 'nullable|string',
            'contactId' => 'required|exists:contacts,id',
            'companyId' => 'nullable|exists:companies,id',
            'expectedCloseDate' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $stage = DealStage::tryFrom($validated['stage'] ?? 'Lead') ?? DealStage::Lead;

        $deal = Deal::create([
            'user_id' => $request->user()->id,
            'contact_id' => $validated['contactId'],
            'company_id' => $validated['companyId'] ?? null,
            'title' => $validated['title'],
            'value' => $validated['value'],
            'stage' => $stage,
            'expected_close_date' => $validated['expectedCloseDate'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'closed_at' => in_array($stage, [DealStage::Won, DealStage::Lost]) ? now() : null,
        ]);

        $deal->load(['contact', 'company']);

        return (new DealResource($deal))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $id): DealResource
    {
        $deal = Deal::findOrFail($id);
        $this->authorize('update', $deal);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'stage' => 'nullable|string',
            'contactId' => 'required|exists:contacts,id',
            'companyId' => 'nullable|exists:companies,id',
            'expectedCloseDate' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $data = [
            'title' => $validated['title'],
            'value' => $validated['value'],
            'contact_id' => $validated['contactId'],
            'company_id' => $validated['companyId'] ?? null,
            'expected_close_date' => $validated['expectedCloseDate'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        if (! empty($validated['stage'])) {
            $newStage = DealStage::tryFrom($validated['stage']);
            if ($newStage) {
                $oldStage = $deal->stage;
                $data['stage'] = $newStage;

                if (in_array($newStage, [DealStage::Won, DealStage::Lost]) && $oldStage !== $newStage) {
                    $data['closed_at'] = now();
                } elseif (! in_array($newStage, [DealStage::Won, DealStage::Lost])) {
                    $data['closed_at'] = null;
                }
            }
        }

        $deal->update($data);
        $deal->load(['contact', 'company']);

        return new DealResource($deal);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $deal = Deal::findOrFail($id);
        $this->authorize('delete', $deal);
        $deal->delete();

        return response()->json(null, 204);
    }

    public function pipeline(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $stages = collect(DealStage::cases())->map(function (DealStage $stage) use ($userId) {
            $deals = Deal::where('user_id', $userId)
                ->where('stage', $stage->value)
                ->with(['contact', 'company'])
                ->get();

            return [
                'stage' => $stage->value,
                'count' => $deals->count(),
                'totalValue' => (float) $deals->sum('value'),
                'deals' => DealResource::collection($deals),
            ];
        });

        return response()->json(['stages' => $stages]);
    }
}
