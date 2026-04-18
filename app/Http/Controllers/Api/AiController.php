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
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function summarizeStream(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'text' => 'required|string',
        ]);

        $prompt = "Summarize the following CRM activity notes in 2-3 concise sentences:\n\n{$validated['text']}";

        return $this->sseStream($prompt);
    }

    public function askStream(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'context' => 'nullable|string',
        ]);

        $context = $validated['context'] ?? '';
        $question = $validated['question'];
        $prompt = $context
            ? "You are a helpful CRM assistant. Answer the following question using the provided context.\n\nContext:\n{$context}\n\nQuestion: {$question}"
            : "You are a helpful CRM assistant. Answer the following question:\n\n{$question}";

        return $this->sseStream($prompt);
    }

    private function sseStream(string $prompt): StreamedResponse
    {
        return new StreamedResponse(function () use ($prompt) {
            $this->ollama->streamToCallback($prompt, function (string $token) {
                echo 'data: '.json_encode(['token' => $token])."\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            });

            echo "data: [DONE]\n\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
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

    public function dealInsightsStream(Request $request, int $dealId): StreamedResponse
    {
        $deal = Deal::findOrFail($dealId);
        $this->authorize('view', $deal);

        $activities = Activity::where('deal_id', $dealId)
            ->orderByDesc('occurred_at')
            ->get();

        $prompt = $this->ollama->buildDealInsightsPrompt($deal, $activities);

        return $this->sseStream($prompt);
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

    public function searchStream(Request $request): StreamedResponse
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

        $prompt = $this->ollama->buildSmartSearchPrompt($validated['query'], $contacts, $activities);

        return $this->sseStream($prompt);
    }
}
