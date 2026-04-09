@section('page-title', $contact->full_name)

<div>
    {{-- Back link --}}
    <a href="{{ route('contacts.index') }}" wire:navigate class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-emerald-500 mb-4">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
        Back to Contacts
    </a>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Contact Info Card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xl font-semibold shrink-0">
                    {{ strtoupper($contact->first_name[0]) }}{{ strtoupper($contact->last_name[0]) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-text-primary">{{ $contact->full_name }}</h2>
                    @if($contact->title)
                        <p class="text-sm text-gray-500">{{ $contact->title }}</p>
                    @endif
                </div>
            </div>

            <dl class="space-y-3 text-sm">
                @if($contact->email)
                    <div class="flex items-center gap-3">
                        <dt class="text-gray-400 w-5 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                        </dt>
                        <dd class="text-gray-700">{{ $contact->email }}</dd>
                    </div>
                @endif
                @if($contact->phone)
                    <div class="flex items-center gap-3">
                        <dt class="text-gray-400 w-5 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>
                        </dt>
                        <dd class="text-gray-700">{{ $contact->phone }}</dd>
                    </div>
                @endif
                @if($contact->company)
                    <div class="flex items-center gap-3">
                        <dt class="text-gray-400 w-5 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                        </dt>
                        <dd>
                            <a href="{{ route('companies.show', $contact->company) }}" wire:navigate class="text-emerald-500 hover:underline">
                                {{ $contact->company->name }}
                            </a>
                        </dd>
                    </div>
                @endif
            </dl>

            @if($contact->tags->isNotEmpty())
                <div class="flex flex-wrap gap-1.5 mt-4 pt-4 border-t border-gray-100">
                    @foreach($contact->tags as $tag)
                        <x-tag-chip :name="$tag->name" :color="$tag->color" />
                    @endforeach
                </div>
            @endif

            @if($contact->notes)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Notes</h4>
                    <p class="text-sm text-gray-600">{{ $contact->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Deals & Activities --}}
        <div class="xl:col-span-2 space-y-6">
            {{-- Deals --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-text-primary mb-4">Deals</h3>
                @if($contact->deals->isEmpty())
                    <p class="text-sm text-gray-500 text-center py-4">No deals associated with this contact.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-gray-200">
                                <tr>
                                    <th class="py-2 text-left font-semibold text-gray-600">Title</th>
                                    <th class="py-2 text-left font-semibold text-gray-600">Value</th>
                                    <th class="py-2 text-left font-semibold text-gray-600">Stage</th>
                                    <th class="py-2 text-left font-semibold text-gray-600 hidden sm:table-cell">Expected Close</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($contact->deals as $deal)
                                    @php
                                        $stageColors = ['Lead' => 'bg-gold/20 text-gold', 'Qualified' => 'bg-brand-orange/20 text-brand-orange', 'Proposal' => 'bg-coral/20 text-coral', 'Negotiation' => 'bg-emerald-500/20 text-emerald-500', 'Won' => 'bg-emerald-600/20 text-emerald-600', 'Lost' => 'bg-gray-200 text-gray-600'];
                                    @endphp
                                    <tr>
                                        <td class="py-2">
                                            <a href="{{ route('deals.show', $deal) }}" wire:navigate class="font-medium text-text-primary hover:text-emerald-500">{{ $deal->title }}</a>
                                        </td>
                                        <td class="py-2 text-gray-600">${{ number_format($deal->value) }}</td>
                                        <td class="py-2">
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $stageColors[$deal->stage->value] ?? '' }}">
                                                {{ $deal->stage->value }}
                                            </span>
                                        </td>
                                        <td class="py-2 text-gray-500 hidden sm:table-cell">
                                            {{ $deal->expected_close_date?->format('M d, Y') ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Activities --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-text-primary mb-4">Activity Timeline</h3>
                @if($contact->activities->isEmpty())
                    <p class="text-sm text-gray-500 text-center py-4">No activities recorded yet.</p>
                @else
                    <div class="space-y-4">
                        @foreach($contact->activities->sortByDesc('occurred_at') as $activity)
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
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
