@section('page-title', 'Companies')

<div>
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition class="mb-4 px-4 py-3 rounded-lg bg-emerald-500/10 text-emerald-500 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <x-page-header title="Companies" subtitle="{{ $companies->total() }} total companies">
        <x-slot name="actions">
            <x-primary-button wire:click="create">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New Company
            </x-primary-button>
        </x-slot>
    </x-page-header>

    {{-- Search --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search companies..."
               class="w-full border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($companies->isEmpty())
            <x-empty-state title="No companies found" message="Create your first company to get started.">
                <x-slot name="icon">
                    <svg class="w-8 h-8 text-brand-orange" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                </x-slot>
                <x-slot name="action">
                    <x-primary-button wire:click="create">New Company</x-primary-button>
                </x-slot>
            </x-empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('name')" class="font-semibold text-gray-600 hover:text-gray-900 flex items-center gap-1">
                                    Name
                                    @if($sortField === 'name')
                                        <svg class="w-3 h-3 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Industry</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 hidden md:table-cell">Phone</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 hidden lg:table-cell">Location</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600 hidden sm:table-cell">Contacts</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600 hidden sm:table-cell">Deals</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($companies as $company)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <a href="{{ route('companies.show', $company) }}" wire:navigate class="font-medium text-text-primary hover:text-emerald-500">
                                        {{ $company->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $company->industry ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600 hidden md:table-cell">{{ $company->phone ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600 hidden lg:table-cell">
                                    @if($company->city && $company->state)
                                        {{ $company->city }}, {{ $company->state }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600 hidden sm:table-cell">{{ $company->contacts_count }}</td>
                                <td class="px-4 py-3 text-center text-gray-600 hidden sm:table-cell">{{ $company->deals_count }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button wire:click="edit({{ $company->id }})" class="p-1.5 text-gray-400 hover:text-emerald-500 rounded-lg hover:bg-gray-100" title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $company->id }})" class="p-1.5 text-gray-400 hover:text-coral rounded-lg hover:bg-gray-100" title="Delete">
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
                {{ $companies->links() }}
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
                    {{ $editingCompanyId ? 'Edit Company' : 'New Company' }}
                </h2>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <x-input-label for="name" value="Company Name" />
                        <x-text-input wire:model="name" id="name" class="block mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="industry" value="Industry" />
                            <x-text-input wire:model="industry" id="industry" class="block mt-1 w-full" />
                        </div>
                        <div>
                            <x-input-label for="phone" value="Phone" />
                            <x-text-input wire:model="phone" id="phone" class="block mt-1 w-full" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="website" value="Website" />
                        <x-text-input wire:model="website" id="website" type="url" class="block mt-1 w-full" placeholder="https://" />
                        <x-input-error :messages="$errors->get('website')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="address" value="Address" />
                        <x-text-input wire:model="address" id="address" class="block mt-1 w-full" />
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="city" value="City" />
                            <x-text-input wire:model="city" id="city" class="block mt-1 w-full" />
                        </div>
                        <div>
                            <x-input-label for="state" value="State" />
                            <x-text-input wire:model="state" id="state" class="block mt-1 w-full" />
                        </div>
                        <div>
                            <x-input-label for="zip" value="ZIP" />
                            <x-text-input wire:model="zip" id="zip" class="block mt-1 w-full" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="notes" value="Notes" />
                        <textarea wire:model="notes" id="notes" rows="3"
                                  class="block mt-1 w-full border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-secondary-button @click="open = false" type="button">Cancel</x-secondary-button>
                        <x-primary-button type="submit">
                            {{ $editingCompanyId ? 'Update' : 'Create' }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirm-dialog wire:model="showDeleteConfirm" title="Delete Company" message="Are you sure? Contacts linked to this company will become unaffiliated.">
        <x-danger-button wire:click="delete">Delete</x-danger-button>
    </x-confirm-dialog>
</div>
