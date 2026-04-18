<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Deal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaService
{
    private string $baseUrl;

    private string $model;

    private bool $enabled;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.ollama.base_url'), '/');
        $this->model = config('services.ollama.model');
        $this->enabled = (bool) config('services.ollama.enabled');
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function summarize(string $text): string
    {
        $prompt = "Summarize the following CRM activity notes in 2-3 concise sentences:\n\n{$text}";

        return $this->generate($prompt);
    }

    public function generateDealInsights(Deal $deal, Collection $activities): string
    {
        $activitySummary = $activities
            ->map(fn (Activity $a) => "- [{$a->type->value}] {$a->subject}: ".($a->body ?? 'No details'))
            ->implode("\n");

        $value = number_format((float) $deal->value, 2);

        $prompt = <<<PROMPT
            Analyze this CRM deal and suggest next steps:

            Deal: {$deal->title}
            Value: \${$value}
            Stage: {$deal->stage->value}

            Recent activity:
            {$activitySummary}

            Provide 3-5 actionable next steps to move this deal forward.
            PROMPT;

        return $this->generate($prompt);
    }

    public function ask(string $question, string $context = ''): string
    {
        $prompt = $context
            ? "You are a helpful CRM assistant. Answer the following question using the provided context.\n\nContext:\n{$context}\n\nQuestion: {$question}"
            : "You are a helpful CRM assistant. Answer the following question:\n\n{$question}";

        return $this->generate($prompt);
    }

    public function smartSearch(string $query, Collection $contacts, Collection $activities): string
    {
        $prompt = $this->buildSmartSearchPrompt($query, $contacts, $activities);

        return $this->generate($prompt);
    }

    public function buildSmartSearchPrompt(string $query, Collection $contacts, Collection $activities): string
    {
        $contactList = $contacts->take(50)
            ->map(fn ($c) => "- ID:{$c->id} {$c->first_name} {$c->last_name} (".($c->email ?? 'no email').') at '.($c->company?->name ?? 'N/A'))
            ->implode("\n");

        $activityList = $activities->take(50)
            ->map(fn ($a) => "- ID:{$a->id} [{$a->type->value}] {$a->subject}")
            ->implode("\n");

        return <<<PROMPT
            Given this search query: "{$query}"

            Find the most relevant contacts and activities from these lists:

            Contacts:
            {$contactList}

            Activities:
            {$activityList}

            Briefly explain what the user is looking for and which records are most relevant.
            PROMPT;
    }

    public function buildDealInsightsPrompt(Deal $deal, Collection $activities): string
    {
        $activitySummary = $activities
            ->map(fn (Activity $a) => "- [{$a->type->value}] {$a->subject}: ".($a->body ?? 'No details'))
            ->implode("\n");

        $value = number_format((float) $deal->value, 2);

        return <<<PROMPT
            Analyze this CRM deal and suggest next steps:

            Deal: {$deal->title}
            Value: \${$value}
            Stage: {$deal->stage->value}

            Recent activity:
            {$activitySummary}

            Provide 3-5 actionable next steps to move this deal forward.
            PROMPT;
    }

    private function generate(string $prompt): string
    {
        if (! $this->enabled) {
            return 'AI features are disabled. Set OLLAMA_ENABLED=true in your .env to enable them.';
        }

        try {
            $response = Http::timeout(300)->post("{$this->baseUrl}/api/generate", [
                'model' => $this->model,
                'prompt' => $prompt,
                'stream' => false,
            ]);

            if (! $response->successful()) {
                Log::error('Ollama API call failed', ['status' => $response->status(), 'body' => $response->body()]);

                return 'AI service is currently unavailable. Please try again later.';
            }

            return $response->json('response', '');
        } catch (\Throwable $e) {
            Log::error('Ollama API exception', ['error' => $e->getMessage()]);

            return 'AI service is currently unavailable. Please try again later.';
        }
    }

    /**
     * Stream tokens from Ollama by calling the callback for each token.
     */
    public function streamToCallback(string $prompt, callable $onToken): void
    {
        if (! $this->enabled) {
            $onToken('AI features are disabled.');

            return;
        }

        try {
            $buffer = '';
            $ch = curl_init("{$this->baseUrl}/api/generate");
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => $this->model,
                    'prompt' => $prompt,
                    'stream' => true,
                ]),
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_WRITEFUNCTION => function ($ch, $data) use ($onToken, &$buffer) {
                    $buffer .= $data;
                    $lines = explode("\n", $buffer);
                    $buffer = array_pop($lines);

                    foreach ($lines as $line) {
                        $line = trim($line);
                        if ($line === '') {
                            continue;
                        }
                        $json = json_decode($line, true);
                        if ($json && ! empty($json['response'])) {
                            $onToken($json['response']);
                        }
                    }

                    return strlen($data);
                },
            ]);

            curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) {
                Log::error('Ollama stream error', ['error' => $err]);
            }
        } catch (\Throwable $e) {
            Log::error('Ollama stream exception', ['error' => $e->getMessage()]);
        }
    }
}
