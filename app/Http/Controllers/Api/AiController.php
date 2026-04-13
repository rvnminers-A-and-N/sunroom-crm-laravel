<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AiInsightResource;
use App\Models\Activity;
use App\Models\AiInsight;
use App\Models\Contact;
use App\Models\Deal;
use App\Services\OllamaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function __construct(private OllamaService $ollama) {}

    public function summarize(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => 'required|string',
        ]);

        $summary = $this->ollama->summarize($validated['text']);

        return response()->json(['summary' => $summary]);
    }

    public function dealInsights(Request $request, int $dealId): JsonResponse
    {
        $deal = Deal::findOrFail($dealId);
        $this->authorize('view', $deal);

        $activities = Activity::where('deal_id', $dealId)
            ->orderByDesc('occurred_at')
            ->get();

        $insightText = $this->ollama->generateDealInsights($deal, $activities);

        $insight = AiInsight::create([
            'deal_id' => $dealId,
            'insight' => $insightText,
            'generated_at' => now(),
        ]);

        return response()->json(new AiInsightResource($insight), 201);
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|max:500',
        ]);

        $userId = $request->user()->id;

        $contacts = Contact::where('user_id', $userId)
            ->with('company')
            ->limit(100)
            ->get();

        $activities = Activity::where('user_id', $userId)
            ->limit(100)
            ->get();

        $result = $this->ollama->smartSearch($validated['query'], $contacts, $activities);

        return response()->json(['interpretation' => $result]);
    }
}
