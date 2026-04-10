<?php

namespace App\Livewire\Deals;

use App\Models\Deal;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DealDetail extends Component
{
    public Deal $deal;

    public function mount(int $id): void
    {
        $this->deal = Deal::with(['contact.company', 'company', 'activities.user', 'aiInsights'])
            ->findOrFail($id);
        $this->authorize('view', $this->deal);
    }

    public function render(): View
    {
        return view('livewire.deals.deal-detail')
            ->title($this->deal->title);
    }
}
