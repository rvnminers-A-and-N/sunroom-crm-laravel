@section('page-title', $deal->title)

@php
    $stageColors = [
        'Lead' => 'bg-gold/20 text-gold',
        'Qualified' => 'bg-brand-orange/20 text-brand-orange',
        'Proposal' => 'bg-coral/20 text-coral',
        'Negotiation' => 'bg-emerald-500/20 text-emerald-500',
        'Won' => 'bg-emerald-600/20 text-emerald-600',
        'Lost' => 'bg-gray-200 text-gray-600',
    ];
@endphp

<div>
    {{-- Back link --}}
    <a href="{{ route('deals.index') }}" wire:navigate class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-emerald-500 mb-4">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
        Back to Deals
    </a>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Deal Info Card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-text-primary">{{ $deal->title }}</h2>
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $stageColors[$deal->stage->value] ?? '' }}">
                    {{ $deal->stage->value }}
                </span>
            </div>

            <div class="text-3xl font-bold text-text-primary mb-6">${{ number_format($deal->value) }}</div>

            <dl class="space-y-3 text-sm">
                @if($deal->contact)
                    <div class="flex items-center gap-3">
                        <dt class="text-gray-400 w-5 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                        </dt>
                        <dd>
                            <a href="{{ route('contacts.show', $deal->contact) }}" wire:navigate class="text-emerald-500 hover:underline">
                                {{ $deal->contact->full_name }}
                            </a>
                            @if($deal->contact->title)
                                <span class="text-gray-400 text-xs ml-1">{{ $deal->contact->title }}</span>
                            @endif
                        </dd>
                    </div>
                @endif
                @if($deal->company)
                    <div class="flex items-center gap-3">
                        <dt class="text-gray-400 w-5 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                        </dt>
                        <dd>
                            <a href="{{ route('companies.show', $deal->company) }}" wire:navigate class="text-emerald-500 hover:underline">
                                {{ $deal->company->name }}
                            </a>
                        </dd>
                    </div>
                @endif
                @if($deal->expected_close_date)
                    <div class="flex items-center gap-3">
                        <dt class="text-gray-400 w-5 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                        </dt>
                        <dd class="text-gray-700">Expected close: {{ $deal->expected_close_date->format('M d, Y') }}</dd>
                    </div>
                @endif
                @if($deal->closed_at)
                    <div class="flex items-center gap-3">
                        <dt class="text-gray-400 w-5 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </dt>
                        <dd class="text-gray-700">Closed: {{ $deal->closed_at->format('M d, Y') }}</dd>
                    </div>
                @endif
            </dl>

            @if($deal->notes)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Notes</h4>
                    <p class="text-sm text-gray-600">{{ $deal->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Activities & AI Insights --}}
        <div class="xl:col-span-2 space-y-6">
            {{-- Activities --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-text-primary mb-4">Activity Timeline</h3>
                @if($deal->activities->isEmpty())
                    <p class="text-sm text-gray-500 text-center py-4">No activities recorded yet.</p>
                @else
                    <div class="space-y-4">
                        @foreach($deal->activities->sortByDesc('occurred_at') as $activity)
                            <div class="flex gap-3">
                                <div class="mt-0.5 shrink-0">
                                    <x-activity-type-icon :type="$activity->type" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-baseline justify-between gap-2">
                                        <p class="text-sm font-medium text-text-primary">{{ $activity->subject }}</p>
                                        <span class="text-xs text-gray-400 shrink-0">{{ $activity->occurred_at->diffForHumans() }}</span>
                                    </div>
                                    @if($activity->body)
                                        <p class="text-sm text-gray-600 mt-0.5">{{ Str::limit($activity->body, 150) }}</p>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-0.5">by {{ $activity->user->name }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- AI Insights --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-text-primary mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-orange" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                    </svg>
                    AI Insights
                </h3>
                @if($deal->aiInsights->isEmpty())
                    <p class="text-sm text-gray-500 text-center py-4">No AI insights generated yet.</p>
                @else
                    <div class="space-y-3">
                        @foreach($deal->aiInsights->sortByDesc('generated_at') as $insight)
                            <div class="p-3 rounded-lg bg-brand-orange/5 border border-brand-orange/20">
                                <p class="text-sm text-text-primary">{{ $insight->insight }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $insight->generated_at->diffForHumans() }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
