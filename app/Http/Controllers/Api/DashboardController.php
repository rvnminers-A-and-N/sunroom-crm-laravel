<?php

namespace App\Http\Controllers\Api;

use App\Enums\DealStage;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $totalContacts = Contact::where('user_id', $userId)->count();
        $totalCompanies = Company::where('user_id', $userId)->count();
        $totalDeals = Deal::where('user_id', $userId)->count();

        $dealsByStageRaw = Deal::where('user_id', $userId)
            ->selectRaw('stage, count(*) as count, sum(value) as total_value')
            ->groupBy('stage')
            ->get();

        $totalPipelineValue = (float) $dealsByStageRaw
            ->whereNotIn('stage', [DealStage::Won->value, DealStage::Lost->value])
            ->sum('total_value');

        $wonRevenue = (float) $dealsByStageRaw
            ->where('stage', DealStage::Won->value)
            ->sum('total_value');

        $dealsByStage = $dealsByStageRaw->map(fn ($row) => [
            'stage' => $row->stage instanceof DealStage ? $row->stage->value : $row->stage,
            'count' => (int) $row->count,
            'totalValue' => (float) $row->total_value,
        ]);

        $recentActivities = Activity::where('user_id', $userId)
            ->with(['contact', 'user'])
            ->orderByDesc('occurred_at')
            ->limit(10)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'type' => $a->type->value,
                'subject' => $a->subject,
                'contactName' => $a->contact ? trim("{$a->contact->first_name} {$a->contact->last_name}") : null,
                'userName' => $a->user?->name ?? '',
                'occurredAt' => $a->occurred_at?->toIso8601String(),
            ]);

        return response()->json([
            'totalContacts' => $totalContacts,
            'totalCompanies' => $totalCompanies,
            'totalDeals' => $totalDeals,
            'totalPipelineValue' => $totalPipelineValue,
            'wonRevenue' => $wonRevenue,
            'dealsByStage' => $dealsByStage,
            'recentActivities' => $recentActivities,
        ]);
    }
}
