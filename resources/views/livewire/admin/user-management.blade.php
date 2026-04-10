@section('page-title', 'User Management')

@php
    $roleColors = [
        'Admin' => 'bg-coral/15 text-coral',
        'Manager' => 'bg-brand-orange/15 text-brand-orange',
        'User' => 'bg-emerald-500/10 text-emerald-500',
    ];
@endphp

<div>
    <x-page-header title="User Management" subtitle="{{ $users->total() }} {{ Str::plural('user', $users->total()) }}">
        <x-slot name="actions">
            <x-primary-button wire:click="create">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New User
            </x-primary-button>
        </x-slot>
    </x-page-header>

    {{-- Search --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search users by name or email..."
               class="w-full border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
    </div>

    {{-- Users Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($users->isEmpty())
            <x-empty-state title="No users found" message="Try adjusting your search.">
                <x-slot name="icon">
                    <svg class="w-8 h-8 text-brand-orange" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </x-slot>
            </x-empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contacts</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Deals</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs font-semibold shrink-0">
                                            {{ collect(explode(' ', $user->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->implode('') }}
                                        </div>
                                        <span class="font-medium text-text-primary">{{ $user->name }}</span>
                                        @if($user->id === auth()->id())
                                            <span class="text-xs text-gray-400">(you)</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $user->email }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $roleColors[$user->role->value] ?? '' }}">
                                        {{ $user->role->value }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $user->contacts_count }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $user->deals_count }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $user->created_at->format('M d, Y') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button wire:click="edit({{ $user->id }})" class="p-1.5 text-gray-400 hover:text-emerald-500 rounded-lg hover:bg-gray-100" title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
                                        </button>
                                        @if($user->id !== auth()->id())
                                            <button wire:click="confirmDelete({{ $user->id }})" class="p-1.5 text-gray-400 hover:text-coral rounded-lg hover:bg-gray-100" title="Delete">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165" /></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- User Form Modal --}}
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
             class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-text-primary mb-4">
                    {{ $editingUserId ? 'Edit User' : 'New User' }}
                </h2>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <x-input-label for="name" value="Name" />
                        <x-text-input wire:model="name" id="name" class="block mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input wire:model="email" id="email" type="email" class="block mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="password" :value="$editingUserId ? 'New Password (leave blank to keep current)' : 'Password'" />
                        <x-text-input wire:model="password" id="password" type="password" class="block mt-1 w-full" :required="!$editingUserId" autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="role" value="Role" />
                        <select wire:model="role" id="role"
                                class="block mt-1 w-full border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @foreach($roles as $r)
                                <option value="{{ $r->value }}">{{ $r->value }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-1" />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-secondary-button @click="open = false" type="button">Cancel</x-secondary-button>
                        <x-primary-button type="submit" wire:loading.attr="disabled" wire:target="save">
                            <svg wire:loading wire:target="save" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            <span wire:loading.remove wire:target="save">{{ $editingUserId ? 'Update' : 'Create' }}</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirm-dialog wire:model="showDeleteConfirm" title="Delete User" message="Are you sure you want to delete this user? All of their contacts and data will be removed.">
        <x-danger-button wire:click="delete">Delete</x-danger-button>
    </x-confirm-dialog>
</div>
