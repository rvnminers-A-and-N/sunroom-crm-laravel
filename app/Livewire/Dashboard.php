<?php

namespace App\Livewire;

use App\Enums\DealStage;
use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render(): View
    {
        $userId = auth()->id();

        $dealsByStage = Deal::where('user_id', $userId)
            ->selectRaw("stage, count(*) as count, sum(value) as total_value")
            ->groupBy('stage')
            ->get()
            ->keyBy('stage');

        return view('livewire.dashboard', [
            'totalContacts' => Contact::where('user_id', $userId)->count(),
            'totalCompanies' => Company::where('user_id', $userId)->count(),
            'totalDeals' => Deal::where('user_id', $userId)->count(),
            'pipelineValue' => Deal::where('user_id', $userId)
                ->whereNotIn('stage', [DealStage::Won, DealStage::Lost])
                ->sum('value'),
            'wonRevenue' => Deal::where('user_id', $userId)
                ->where('stage', DealStage::Won)
                ->sum('value'),
            'dealsByStage' => $dealsByStage,
            'stages' => collect(DealStage::cases()),
            'recentActivities' => Activity::where('user_id', $userId)
                ->with(['contact', 'deal'])
                ->latest('occurred_at')
                ->take(10)
                ->get(),
        ]);
    }
}
