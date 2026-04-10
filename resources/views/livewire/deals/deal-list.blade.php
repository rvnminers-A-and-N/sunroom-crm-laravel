@section('page-title', 'Deals')

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
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition class="mb-4 px-4 py-3 rounded-lg bg-emerald-500/10 text-emerald-500 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <x-page-header title="Deals" subtitle="{{ $deals->total() }} total deals">
        <x-slot name="actions">
            <a href="{{ route('deals.pipeline') }}" wire:navigate
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                Pipeline
            </a>
            <x-primary-button wire:click="create">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New Deal
            </x-primary-button>
        </x-slot>
    </x-page-header>

    {{-- Search & Stage Filter --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search deals..."
                   class="flex-1 border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
            <select wire:model.live="stageFilter"
                    class="border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">All Stages</option>
                @foreach($stages as $stage)
                    <option value="{{ $stage->value }}">{{ $stage->value }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($deals->isEmpty())
            <x-empty-state title="No deals found" message="Create your first deal to start tracking your pipeline.">
                <x-slot name="icon">
                    <svg class="w-8 h-8 text-brand-orange" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </x-slot>
                <x-slot name="action">
                    <x-primary-button wire:click="create">New Deal</x-primary-button>
                </x-slot>
            </x-empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('title')" class="font-semibold text-gray-600 hover:text-gray-900 flex items-center gap-1">
                                    Title
                                    @if($sortField === 'title')
                                        <svg class="w-3 h-3 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 hidden sm:table-cell">Contact</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 hidden md:table-cell">Company</th>
                            <th class="px-4 py-3 text-right">
                                <button wire:click="sortBy('value')" class="font-semibold text-gray-600 hover:text-gray-900 flex items-center gap-1 ml-auto">
                                    Value
                                    @if($sortField === 'value')
                                        <svg class="w-3 h-3 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Stage</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 hidden lg:table-cell">Expected Close</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($deals as $deal)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <a href="{{ route('deals.show', $deal) }}" wire:navigate class="font-medium text-text-primary hover:text-emerald-500">
                                        {{ $deal->title }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-600 hidden sm:table-cell">
                                    @if($deal->contact)
                                        <a href="{{ route('contacts.show', $deal->contact) }}" wire:navigate class="hover:text-emerald-500">
                                            {{ $deal->contact->full_name }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 hidden md:table-cell">
                                    @if($deal->company)
                                        <a href="{{ route('companies.show', $deal->company) }}" wire:navigate class="hover:text-emerald-500">
                                            {{ $deal->company->name }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-gray-700">${{ number_format($deal->value) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $stageColors[$deal->stage->value] ?? '' }}">
                                        {{ $deal->stage->value }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 hidden lg:table-cell">
                                    {{ $deal->expected_close_date?->format('M d, Y') ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button wire:click="edit({{ $deal->id }})" class="p-1.5 text-gray-400 hover:text-emerald-500 rounded-lg hover:bg-gray-100" title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $deal->id }})" class="p-1.5 text-gray-400 hover:text-coral rounded-lg hover:bg-gray-100" title="Delete">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-gray-200">
                {{ $deals->links() }}
            </div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    <div x-data="{ open: @entangle('showForm').live }"
         x-show="open" x-cloak class="fixed inset-0 z-50 flex items-start justify-center pt-16 sm:pt-24">
        <div x-show="open" @click="open = false"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50"></div>

        <div x-show="open"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             @click.away="open = false"
             class="relative bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 max-h-[80vh] overflow-y-auto">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-text-primary mb-4">
                    {{ $editingDealId ? 'Edit Deal' : 'New Deal' }}
                </h2>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <x-input-label for="title" value="Deal Title" />
                        <x-text-input wire:model="title" id="title" class="block mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('title')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="value" value="Value ($)" />
                            <x-text-input wire:model="value" id="value" type="number" step="0.01" min="0" class="block mt-1 w-full" required />
                            <x-input-error :messages="$errors->get('value')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="stage" value="Stage" />
                            <select wire:model="stage" id="stage"
                                    class="block mt-1 w-full border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                @foreach($stages as $s)
                                    <option value="{{ $s->value }}">{{ $s->value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="contactId" value="Contact" />
                        <select wire:model="contactId" id="contactId"
                                class="block mt-1 w-full border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                            <option value="">Select a contact...</option>
                            @foreach($contacts as $contact)
                                <option value="{{ $contact->id }}">{{ $contact->full_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('contactId')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="companyId" value="Company (Optional)" />
                        <select wire:model="companyId" id="companyId"
                                class="block mt-1 w-full border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">None</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="expectedCloseDate" value="Expected Close Date" />
                        <x-text-input wire:model="expectedCloseDate" id="expectedCloseDate" type="date" class="block mt-1 w-full" />
                    </div>

                    <div>
                        <x-input-label for="notes" value="Notes" />
                        <textarea wire:model="notes" id="notes" rows="3"
                                  class="block mt-1 w-full border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-secondary-button @click="open = false" type="button">Cancel</x-secondary-button>
                        <x-primary-button type="submit">
                            {{ $editingDealId ? 'Update' : 'Create' }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirm-dialog wire:model="showDeleteConfirm" title="Delete Deal" message="Are you sure you want to delete this deal? This action cannot be undone.">
        <x-danger-button wire:click="delete">Delete</x-danger-button>
    </x-confirm-dialog>
</div>
