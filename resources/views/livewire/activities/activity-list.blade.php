@section('page-title', 'Activities')

<div>
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition class="mb-4 px-4 py-3 rounded-lg bg-emerald-500/10 text-emerald-500 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <x-page-header title="Activities" subtitle="{{ $activities->total() }} total activities">
        <x-slot name="actions">
            <x-primary-button wire:click="create">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Log Activity
            </x-primary-button>
        </x-slot>
    </x-page-header>

    {{-- Type Filter Tabs --}}
    <div class="bg-white rounded-xl border border-gray-200 p-2 mb-4">
        <div class="flex flex-wrap gap-1">
            <button wire:click="setTypeFilter('')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $typeFilter === '' ? 'bg-emerald-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                All
            </button>
            @foreach($types as $t)
                <button wire:click="setTypeFilter('{{ $t->value }}')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5 {{ $typeFilter === $t->value ? 'bg-emerald-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    @if($t->value === 'Note')
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                    @elseif($t->value === 'Call')
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>
                    @elseif($t->value === 'Email')
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                    @elseif($t->value === 'Meeting')
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                    @elseif($t->value === 'Task')
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    @endif
                    {{ $t->value }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Search & Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search activities..."
                   class="flex-1 border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
            <select wire:model.live="contactFilter"
                    class="border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">All Contacts</option>
                @foreach($contacts as $contact)
                    <option value="{{ $contact->id }}">{{ $contact->full_name }}</option>
                @endforeach
            </select>
            <select wire:model.live="dealFilter"
                    class="border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">All Deals</option>
                @foreach($deals as $deal)
                    <option value="{{ $deal->id }}">{{ $deal->title }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($activities->isEmpty())
            <x-empty-state title="No activities found" message="Log your first activity to start building your timeline.">
                <x-slot name="icon">
                    <svg class="w-8 h-8 text-brand-orange" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                </x-slot>
                <x-slot name="action">
                    <x-primary-button wire:click="create">Log Activity</x-primary-button>
                </x-slot>
            </x-empty-state>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($activities as $activity)
                    <div class="p-4 hover:bg-gray-50 transition-colors group">
                        <div class="flex gap-4">
                            <div class="shrink-0 mt-0.5">
                                <x-activity-type-icon :type="$activity->type" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                {{ $activity->type->value }}
                                            </span>
                                            <h3 class="font-semibold text-text-primary">{{ $activity->subject }}</h3>
                                        </div>
                                        @if($activity->body)
                                            <p class="text-sm text-gray-600 mt-1">{{ $activity->body }}</p>
                                        @endif
                                        <div class="flex items-center gap-3 mt-2 text-xs text-gray-400 flex-wrap">
                                            <span>{{ $activity->occurred_at->format('M d, Y g:i A') }} ({{ $activity->occurred_at->diffForHumans() }})</span>
                                            @if($activity->contact)
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                                    <a href="{{ route('contacts.show', $activity->contact) }}" wire:navigate class="hover:text-emerald-500">
                                                        {{ $activity->contact->full_name }}
                                                    </a>
                                                </span>
                                            @endif
                                            @if($activity->deal)
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    <a href="{{ route('deals.show', $activity->deal) }}" wire:navigate class="hover:text-emerald-500">
                                                        {{ $activity->deal->title }}
                                                    </a>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                                        <button wire:click="edit({{ $activity->id }})" class="p-1.5 text-gray-400 hover:text-emerald-500 rounded-lg hover:bg-white" title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $activity->id }})" class="p-1.5 text-gray-400 hover:text-coral rounded-lg hover:bg-white" title="Delete">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="px-4 py-3 border-t border-gray-200">
                {{ $activities->links() }}
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
                    {{ $editingActivityId ? 'Edit Activity' : 'Log Activity' }}
                </h2>

                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="type" value="Type" />
                            <select wire:model="type" id="type"
                                    class="block mt-1 w-full border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                @foreach($types as $t)
                                    <option value="{{ $t->value }}">{{ $t->value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="occurredAt" value="Date & Time" />
                            <x-text-input wire:model="occurredAt" id="occurredAt" type="datetime-local" class="block mt-1 w-full" required />
                            <x-input-error :messages="$errors->get('occurredAt')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="subject" value="Subject" />
                        <x-text-input wire:model="subject" id="subject" class="block mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('subject')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="body" value="Notes" />
                        <textarea wire:model="body" id="body" rows="4"
                                  class="block mt-1 w-full border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>

                    <div>
                        <x-input-label for="contactId" value="Related Contact (Optional)" />
                        <select wire:model="contactId" id="contactId"
                                class="block mt-1 w-full border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">None</option>
                            @foreach($contacts as $contact)
                                <option value="{{ $contact->id }}">{{ $contact->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="dealId" value="Related Deal (Optional)" />
                        <select wire:model="dealId" id="dealId"
                                class="block mt-1 w-full border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">None</option>
                            @foreach($deals as $deal)
                                <option value="{{ $deal->id }}">{{ $deal->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-secondary-button @click="open = false" type="button">Cancel</x-secondary-button>
                        <x-primary-button type="submit">
                            {{ $editingActivityId ? 'Update' : 'Log Activity' }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirm-dialog wire:model="showDeleteConfirm" title="Delete Activity" message="Are you sure you want to delete this activity? This action cannot be undone.">
        <x-danger-button wire:click="delete">Delete</x-danger-button>
    </x-confirm-dialog>
</div>
