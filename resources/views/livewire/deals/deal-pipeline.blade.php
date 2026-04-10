@section('page-title', 'Deal Pipeline')

@php
    $stageColors = [
        'Lead' => ['bg' => 'bg-gold/20', 'text' => 'text-gold', 'border' => 'border-gold', 'dot' => 'bg-gold'],
        'Qualified' => ['bg' => 'bg-brand-orange/20', 'text' => 'text-brand-orange', 'border' => 'border-brand-orange', 'dot' => 'bg-brand-orange'],
        'Proposal' => ['bg' => 'bg-coral/20', 'text' => 'text-coral', 'border' => 'border-coral', 'dot' => 'bg-coral'],
        'Negotiation' => ['bg' => 'bg-emerald-500/20', 'text' => 'text-emerald-500', 'border' => 'border-emerald-500', 'dot' => 'bg-emerald-500'],
        'Won' => ['bg' => 'bg-emerald-600/20', 'text' => 'text-emerald-600', 'border' => 'border-emerald-600', 'dot' => 'bg-emerald-600'],
        'Lost' => ['bg' => 'bg-gray-200', 'text' => 'text-gray-600', 'border' => 'border-gray-400', 'dot' => 'bg-gray-400'],
    ];
@endphp

<div>
    <x-page-header title="Deal Pipeline" subtitle="{{ $dealsByStage->flatten()->count() }} total deals">
        <x-slot name="actions">
            <a href="{{ route('deals.index') }}" wire:navigate
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
                List View
            </a>
        </x-slot>
    </x-page-header>

    {{-- Kanban Board --}}
    <div class="flex gap-4 overflow-x-auto pb-4" x-data="pipeline()" x-init="init()">
        @foreach($stages as $stage)
            @php
                $colors = $stageColors[$stage->value];
                $stageDeals = $dealsByStage->get($stage->value, collect());
                $stageTotal = $stageDeals->sum('value');
            @endphp
            <div class="flex-shrink-0 w-72">
                {{-- Column Header --}}
                <div class="flex items-center justify-between mb-3 px-1">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full {{ $colors['dot'] }}"></div>
                        <h3 class="font-semibold text-sm text-text-primary">{{ $stage->value }}</h3>
                        <span class="text-xs text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded-full">{{ $stageDeals->count() }}</span>
                    </div>
                    <span class="text-xs font-medium text-gray-500">${{ number_format($stageTotal) }}</span>
                </div>

                {{-- Column Body (Sortable) --}}
                <div class="space-y-2 min-h-[200px] p-2 rounded-xl bg-gray-50 border border-gray-200 pipeline-column"
                     data-stage="{{ $stage->value }}">
                    @foreach($stageDeals as $deal)
                        <div class="bg-white rounded-lg border border-gray-200 p-3 cursor-grab active:cursor-grabbing shadow-sm hover:shadow transition-shadow pipeline-card"
                             data-deal-id="{{ $deal->id }}">
                            <a href="{{ route('deals.show', $deal) }}" wire:navigate class="font-medium text-sm text-text-primary hover:text-emerald-500 block mb-1">
                                {{ $deal->title }}
                            </a>
                            <div class="text-lg font-bold text-text-primary">${{ number_format($deal->value) }}</div>
                            @if($deal->contact)
                                <div class="text-xs text-gray-500 mt-1.5 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                    {{ $deal->contact->full_name }}
                                </div>
                            @endif
                            @if($deal->company)
                                <div class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                                    {{ $deal->company->name }}
                                </div>
                            @endif
                            @if($deal->expected_close_date)
                                <div class="text-xs text-gray-400 mt-1.5">
                                    Close: {{ $deal->expected_close_date->format('M d, Y') }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
    function pipeline() {
        return {
            init() {
                this.$nextTick(() => {
                    document.querySelectorAll('.pipeline-column').forEach(column => {
                        new Sortable(column, {
                            group: 'pipeline',
                            animation: 150,
                            ghostClass: 'opacity-30',
                            dragClass: 'shadow-lg',
                            handle: '.pipeline-card',
                            onEnd: (evt) => {
                                const dealId = parseInt(evt.item.dataset.dealId);
                                const newStage = evt.to.dataset.stage;
                                @this.call('updateStage', dealId, newStage);
                            }
                        });
                    });
                });
            }
        };
    }
</script>
@endpush
