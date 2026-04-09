@section('page-title', $company->name)

<div>
    {{-- Back link --}}
    <a href="{{ route('companies.index') }}" wire:navigate class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-emerald-500 mb-4">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
        Back to Companies
    </a>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Company Info Card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 rounded-xl bg-emerald-500/10 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-text-primary">{{ $company->name }}</h2>
                    @if($company->industry)
                        <p class="text-sm text-gray-500">{{ $company->industry }}</p>
                    @endif
                </div>
            </div>

            <dl class="space-y-3 text-sm">
                @if($company->phone)
                    <div class="flex items-center gap-3">
                        <dt class="text-gray-400 w-5 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>
                        </dt>
                        <dd class="text-gray-700">{{ $company->phone }}</dd>
                    </div>
                @endif
                @if($company->website)
                    <div class="flex items-center gap-3">
                        <dt class="text-gray-400 w-5 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" /></svg>
                        </dt>
                        <dd>
                            <a href="{{ $company->website }}" target="_blank" rel="noopener" class="text-emerald-500 hover:underline">{{ $company->website }}</a>
                        </dd>
                    </div>
                @endif
                @if($company->address || $company->city || $company->state)
                    <div class="flex items-start gap-3">
                        <dt class="text-gray-400 w-5 shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 0115 0z" /></svg>
                        </dt>
                        <dd class="text-gray-700">
                            @if($company->address)<div>{{ $company->address }}</div>@endif
                            @if($company->city && $company->state)
                                <div>{{ $company->city }}, {{ $company->state }} {{ $company->zip }}</div>
                            @elseif($company->city)
                                <div>{{ $company->city }} {{ $company->zip }}</div>
                            @elseif($company->state)
                                <div>{{ $company->state }} {{ $company->zip }}</div>
                            @endif
                        </dd>
                    </div>
                @endif
            </dl>

            @if($company->notes)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Notes</h4>
                    <p class="text-sm text-gray-600">{{ $company->notes }}</p>
                </div>
            @endif

            <div class="mt-4 pt-4 border-t border-gray-100 flex gap-2 text-sm">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-gray-50 text-gray-600">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                    {{ $company->contacts_count }} {{ Str::plural('contact', $company->contacts_count) }}
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-gray-50 text-gray-600">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ $company->deals_count }} {{ Str::plural('deal', $company->deals_count) }}
                </span>
            </div>
        </div>

        {{-- Contacts & Deals --}}
        <div class="xl:col-span-2 space-y-6">
            {{-- Contacts --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-text-primary mb-4">Contacts</h3>
                @if($company->contacts->isEmpty())
                    <p class="text-sm text-gray-500 text-center py-4">No contacts linked to this company.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-gray-200">
                                <tr>
                                    <th class="py-2 text-left font-semibold text-gray-600">Name</th>
                                    <th class="py-2 text-left font-semibold text-gray-600 hidden sm:table-cell">Email</th>
                                    <th class="py-2 text-left font-semibold text-gray-600 hidden md:table-cell">Phone</th>
                                    <th class="py-2 text-left font-semibold text-gray-600 hidden lg:table-cell">Tags</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($company->contacts as $contact)
                                    <tr>
                                        <td class="py-2">
                                            <a href="{{ route('contacts.show', $contact) }}" wire:navigate class="font-medium text-text-primary hover:text-emerald-500">
                                                {{ $contact->full_name }}
                                            </a>
                                            @if($contact->title)
                                                <div class="text-xs text-gray-400">{{ $contact->title }}</div>
                                            @endif
                                        </td>
                                        <td class="py-2 text-gray-600 hidden sm:table-cell">{{ $contact->email ?? '—' }}</td>
                                        <td class="py-2 text-gray-600 hidden md:table-cell">{{ $contact->phone ?? '—' }}</td>
                                        <td class="py-2 hidden lg:table-cell">
                                            @if($contact->tags->isNotEmpty())
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($contact->tags as $tag)
                                                        <x-tag-chip :name="$tag->name" :color="$tag->color" />
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Deals --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-text-primary mb-4">Deals</h3>
                @if($company->deals->isEmpty())
                    <p class="text-sm text-gray-500 text-center py-4">No deals associated with this company.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-gray-200">
                                <tr>
                                    <th class="py-2 text-left font-semibold text-gray-600">Title</th>
                                    <th class="py-2 text-left font-semibold text-gray-600">Contact</th>
                                    <th class="py-2 text-left font-semibold text-gray-600">Value</th>
                                    <th class="py-2 text-left font-semibold text-gray-600">Stage</th>
                                    <th class="py-2 text-left font-semibold text-gray-600 hidden sm:table-cell">Expected Close</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($company->deals as $deal)
                                    @php
                                        $stageColors = ['Lead' => 'bg-gold/20 text-gold', 'Qualified' => 'bg-brand-orange/20 text-brand-orange', 'Proposal' => 'bg-coral/20 text-coral', 'Negotiation' => 'bg-emerald-500/20 text-emerald-500', 'Won' => 'bg-emerald-600/20 text-emerald-600', 'Lost' => 'bg-gray-200 text-gray-600'];
                                    @endphp
                                    <tr>
                                        <td class="py-2">
                                            <a href="{{ route('deals.show', $deal) }}" wire:navigate class="font-medium text-text-primary hover:text-emerald-500">{{ $deal->title }}</a>
                                        </td>
                                        <td class="py-2 text-gray-600">
                                            @if($deal->contact)
                                                <a href="{{ route('contacts.show', $deal->contact) }}" wire:navigate class="hover:text-emerald-500">{{ $deal->contact->full_name }}</a>
                                            @else
                                                —
                                            @endif
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
        </div>
    </div>
</div>
