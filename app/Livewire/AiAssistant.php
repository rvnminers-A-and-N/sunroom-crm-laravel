<?php

namespace App\Livewire;

use App\Models\Activity;
use App\Models\Contact;
use App\Services\OllamaService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('AI Assistant')]
class AiAssistant extends Component
{
    public string $question = '';

    public array $messages = [];

    public bool $loading = false;

    public string $searchQuery = '';

    public string $searchResult = '';

    public bool $searchLoading = false;

    public int|string $insightsDealId = '';

    public string $insightsResult = '';

    public bool $insightsLoading = false;

    public function prepareAsk(): ?array
    {
        $this->validate([
            'question' => 'required|string|max:500',
        ]);

        $userQuestion = $this->question;
        $this->messages[] = ['role' => 'user', 'content' => $userQuestion];
        $this->question = '';
        $this->loading = true;

        $userId = auth()->id();
        $contactCount = Contact::where('user_id', $userId)->count();
        $recentContacts = Contact::where('user_id', $userId)
            ->with('company')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($c) => "- {$c->first_name} {$c->last_name}".($c->company ? " at {$c->company->name}" : ''))
            ->implode("\n");

        $recentActivities = Activity::where('user_id', $userId)
            ->with('contact')
            ->orderByDesc('occurred_at')
            ->limit(20)
            ->get()
            ->map(fn ($a) => "- [{$a->type->value}] {$a->subject}".($a->contact ? " (with {$a->contact->first_name} {$a->contact->last_name})" : ''))
            ->implode("\n");

        $context = "You have {$contactCount} contacts.\n\nRecent contacts:\n{$recentContacts}\n\nRecent activities:\n{$recentActivities}";

        return ['question' => $userQuestion, 'context' => $context];
    }

    public function finishAsk(string $answer): void
    {
        $this->messages[] = ['role' => 'assistant', 'content' => $answer];
        $this->loading = false;
    }

    public function prepareSearch(): ?array
    {
        $this->validate([
            'searchQuery' => 'required|string|max:500',
        ]);

        $query = $this->searchQuery;
        $this->searchLoading = true;
        $this->searchResult = '';

        return ['query' => $query];
    }

    public function finishSearch(string $result): void
    {
        $this->searchResult = $result;
        $this->searchLoading = false;
    }

    public function prepareDealInsights(): ?array
    {
        $this->validate([
            'insightsDealId' => 'required|integer|exists:deals,id',
        ]);

        $dealId = (int) $this->insightsDealId;
        $this->insightsLoading = true;
        $this->insightsResult = '';

        return ['dealId' => $dealId];
    }

    public function finishDealInsights(string $result): void
    {
        $this->insightsResult = $result;
        $this->insightsLoading = false;
    }

    public function clearChat(): void
    {
        $this->messages = [];
    }

    public function render(): View
    {
        return view('livewire.ai-assistant', [
            'aiEnabled' => app(OllamaService::class)->isEnabled(),
        ]);
    }
}
