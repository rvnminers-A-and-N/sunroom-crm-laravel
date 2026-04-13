<?php

namespace App\Livewire\Deals;

use App\Models\AiInsight;
use App\Models\Deal;
use App\Services\OllamaService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DealDetail extends Component
{
    public Deal $deal;

    public bool $generatingInsight = false;

    public function mount(int $id): void
    {
        $this->deal = Deal::with(['contact.company', 'company', 'activities.user', 'aiInsights'])
            ->findOrFail($id);
        $this->authorize('view', $this->deal);
    }

    public function generateInsight(OllamaService $ollama): void
    {
        $this->authorize('view', $this->deal);
        $this->generatingInsight = true;

        $activities = $this->deal->activities()->orderByDesc('occurred_at')->get();
        $insightText = $ollama->generateDealInsights($this->deal, $activities);

        AiInsight::create([
            'deal_id' => $this->deal->id,
            'insight' => $insightText,
            'generated_at' => now(),
        ]);

        $this->deal->load('aiInsights');
        $this->generatingInsight = false;
        session()->flash('success', 'AI insight generated.');
    }

    public function render(): View
    {
        return view('livewire.deals.deal-detail', [
            'aiEnabled' => app(OllamaService::class)->isEnabled(),
        ])->title($this->deal->title);
    }
}
