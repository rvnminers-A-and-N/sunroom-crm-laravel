<?php

namespace App\Livewire\Deals;

use App\Enums\DealStage;
use App\Models\Deal;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Deal Pipeline')]
class DealPipeline extends Component
{
    public function updateStage(int $dealId, string $stage): void
    {
        $deal = Deal::findOrFail($dealId);
        $this->authorize('update', $deal);

        $newStage = DealStage::from($stage);

        $data = ['stage' => $newStage];

        // Auto-set closed_at for Won/Lost, clear for other stages
        if (in_array($newStage, [DealStage::Won, DealStage::Lost])) {
            $data['closed_at'] = now();
        } else {
            $data['closed_at'] = null;
        }

        $deal->update($data);
    }

    public function render(): View
    {
        $deals = Deal::where('user_id', auth()->id())
            ->with(['contact', 'company'])
            ->get()
            ->groupBy(fn ($deal) => $deal->stage->value);

        return view('livewire.deals.deal-pipeline', [
            'stages' => DealStage::cases(),
            'dealsByStage' => $deals,
        ]);
    }
}
