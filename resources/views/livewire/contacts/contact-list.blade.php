@section('page-title', 'Contacts')

<div>
    {{-- Header --}}
    <x-page-header title="Contacts" subtitle="{{ $contacts->total() }} total contacts">
        <x-slot name="actions">
            <x-primary-button wire:click="create">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New Contact
            </x-primary-button>
        </x-slot>
    </x-page-header>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search contacts..."
                       class="w-full border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <select wire:model.live="companyFilter" class="border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">All Companies</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="tagFilter" class="border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">All Tags</option>
                @foreach($tags as $tag)
                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($contacts->isEmpty())
            <x-empty-state title="No contacts found" message="Create your first contact to get started.">
                <x-slot name="icon">
                    <svg class="w-8 h-8 text-brand-orange" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </x-slot>
                <x-slot name="action">
                    <x-primary-button wire:click="create">New Contact</x-primary-button>
                </x-slot>
            </x-empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="sortBy('first_name')" class="font-semibold text-gray-600 hover:text-gray-900 flex items-center gap-1">
                                    Name
                                    @if($sortField === 'first_name')
                                        <svg class="w-3 h-3 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Email</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 hidden md:table-cell">Phone</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 hidden lg:table-cell">Company</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 hidden xl:table-cell">Tags</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($contacts as $contact)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <a href="{{ route('contacts.show', $contact) }}" wire:navigate class="font-medium text-text-primary hover:text-emerald-500">
                                        {{ $contact->full_name }}
                                    </a>
                                    @if($contact->title)
                                        <p class="text-xs text-gray-500">{{ $contact->title }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $contact->email }}</td>
                                <td class="px-4 py-3 text-gray-600 hidden md:table-cell">{{ $contact->phone }}</td>
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    @if($contact->company)
                                        <span class="text-gray-600">{{ $contact->company->name }}</span>
                                    @else
                                        <span class="text-gray-400">&mdash;</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 hidden xl:table-cell">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($contact->tags as $tag)
                                            <x-tag-chip :name="$tag->name" :color="$tag->color" />
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button wire:click="edit({{ $contact->id }})" class="p-1.5 text-gray-400 hover:text-emerald-500 rounded-lg hover:bg-gray-100" title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $contact->id }})" class="p-1.5 text-gray-400 hover:text-coral rounded-lg hover:bg-gray-100" title="Delete">
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
                {{ $contacts->links() }}
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
                    {{ $editingContactId ? 'Edit Contact' : 'New Contact' }}
                </h2>

                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="firstName" value="First Name" />
                            <x-text-input wire:model="firstName" id="firstName" class="block mt-1 w-full" required />
                            <x-input-error :messages="$errors->get('firstName')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="lastName" value="Last Name" />
                            <x-text-input wire:model="lastName" id="lastName" class="block mt-1 w-full" required />
                            <x-input-error :messages="$errors->get('lastName')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input wire:model="email" id="email" type="email" class="block mt-1 w-full" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="phone" value="Phone" />
                            <x-text-input wire:model="phone" id="phone" class="block mt-1 w-full" />
                        </div>
                        <div>
                            <x-input-label for="title" value="Title" />
                            <x-text-input wire:model="title" id="title" class="block mt-1 w-full" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="companyId" value="Company" />
                        <select wire:model="companyId" id="companyId" class="block mt-1 w-full border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">No Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label value="Tags" />
                        <div class="flex flex-wrap gap-2 mt-1">
                            @foreach($tags as $tag)
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" wire:model="tagIds" value="{{ $tag->id }}"
                                           class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                                    <x-tag-chip :name="$tag->name" :color="$tag->color" />
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <x-input-label for="notes" value="Notes" />
                        <textarea wire:model="notes" id="notes" rows="3"
                                  class="block mt-1 w-full border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-secondary-button @click="open = false" type="button">Cancel</x-secondary-button>
                        <x-primary-button type="submit" wire:loading.attr="disabled" wire:target="save">
                            <svg wire:loading wire:target="save" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            <span wire:loading.remove wire:target="save">{{ $editingContactId ? 'Update' : 'Create' }}</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation --}}
    <x-confirm-dialog wire:model="showDeleteConfirm" title="Delete Contact" message="Are you sure you want to delete this contact? This action cannot be undone.">
        <x-danger-button wire:click="delete">Delete</x-danger-button>
    </x-confirm-dialog>
</div>
