@section('page-title', 'Settings')

<div>
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition class="mb-4 px-4 py-3 rounded-lg bg-emerald-500/10 text-emerald-500 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <x-page-header title="Settings" subtitle="Manage your account and workspace" />

    {{-- Tabs --}}
    <div class="bg-white rounded-xl border border-gray-200 p-2 mb-4">
        <div class="flex flex-wrap gap-1">
            <button wire:click="setTab('profile')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5 {{ $tab === 'profile' ? 'bg-emerald-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
                Profile
            </button>
            <button wire:click="setTab('password')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5 {{ $tab === 'password' ? 'bg-emerald-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
                Password
            </button>
            <button wire:click="setTab('tags')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5 {{ $tab === 'tags' ? 'bg-emerald-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                </svg>
                Tags
            </button>
        </div>
    </div>

    {{-- Profile Tab --}}
    @if($tab === 'profile')
        <div class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-text-primary">Profile Information</h2>
                <p class="text-sm text-gray-500 mt-0.5">Update your account's profile information.</p>
            </div>

            <form wire:submit="updateProfile" class="space-y-4">
                <div>
                    <x-input-label for="name" value="Name" />
                    <x-text-input wire:model="name" id="name" class="block mt-1 w-full" required autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input wire:model="email" id="email" type="email" class="block mt-1 w-full" required autocomplete="email" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div class="pt-2">
                    <x-primary-button type="submit">Save Changes</x-primary-button>
                </div>
            </form>
        </div>
    @endif

    {{-- Password Tab --}}
    @if($tab === 'password')
        <div class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-text-primary">Change Password</h2>
                <p class="text-sm text-gray-500 mt-0.5">Use a long, random password to keep your account secure.</p>
            </div>

            <form wire:submit="updatePassword" class="space-y-4">
                <div>
                    <x-input-label for="currentPassword" value="Current Password" />
                    <x-text-input wire:model="currentPassword" id="currentPassword" type="password" class="block mt-1 w-full" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('currentPassword')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="newPassword" value="New Password" />
                    <x-text-input wire:model="newPassword" id="newPassword" type="password" class="block mt-1 w-full" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('newPassword')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="newPasswordConfirmation" value="Confirm New Password" />
                    <x-text-input wire:model="newPasswordConfirmation" id="newPasswordConfirmation" type="password" class="block mt-1 w-full" required autocomplete="new-password" />
                </div>

                <div class="pt-2">
                    <x-primary-button type="submit">Update Password</x-primary-button>
                </div>
            </form>
        </div>
    @endif

    {{-- Tags Tab --}}
    @if($tab === 'tags')
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-text-primary">Tags</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Organize your contacts with custom tags.</p>
                </div>
                <x-primary-button wire:click="createTag">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    New Tag
                </x-primary-button>
            </div>

            @if($tags->isEmpty())
                <div class="text-center py-12">
                    <p class="text-sm text-gray-500">No tags yet. Create your first tag to organize contacts.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($tags as $tag)
                        <div class="group flex items-center justify-between gap-2 p-3 rounded-lg border border-gray-200 hover:border-emerald-500/50 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="inline-block w-3 h-3 rounded-full shrink-0" style="background-color: {{ $tag->color }};"></span>
                                <span class="text-sm font-medium text-text-primary truncate">{{ $tag->name }}</span>
                                <span class="text-xs text-gray-400 shrink-0">{{ $tag->contacts_count }}</span>
                            </div>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                                <button wire:click="editTag({{ $tag->id }})" class="p-1.5 text-gray-400 hover:text-emerald-500 rounded-lg hover:bg-white" title="Edit">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
                                </button>
                                <button wire:click="confirmDeleteTag({{ $tag->id }})" class="p-1.5 text-gray-400 hover:text-coral rounded-lg hover:bg-white" title="Delete">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165" /></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Tag Form Modal --}}
        <div x-data="{ open: @entangle('showTagForm').live }"
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
                        {{ $editingTagId ? 'Edit Tag' : 'New Tag' }}
                    </h2>

                    <form wire:submit="saveTag" class="space-y-4">
                        <div>
                            <x-input-label for="tagName" value="Name" />
                            <x-text-input wire:model="tagName" id="tagName" class="block mt-1 w-full" required maxlength="50" />
                            <x-input-error :messages="$errors->get('tagName')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="tagColor" value="Color" />
                            <div class="flex items-center gap-3 mt-1">
                                <input wire:model="tagColor" id="tagColor" type="color"
                                       class="h-10 w-16 border border-gray-300 rounded-lg cursor-pointer p-1" />
                                <x-text-input wire:model="tagColor" type="text" class="flex-1" required maxlength="7" />
                            </div>
                            <x-input-error :messages="$errors->get('tagColor')" class="mt-1" />
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <x-secondary-button @click="open = false" type="button">Cancel</x-secondary-button>
                            <x-primary-button type="submit">
                                {{ $editingTagId ? 'Update' : 'Create' }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <x-confirm-dialog wire:model="showDeleteConfirm" title="Delete Tag" message="Are you sure you want to delete this tag? It will be removed from all contacts.">
            <x-danger-button wire:click="deleteTag">Delete</x-danger-button>
        </x-confirm-dialog>
    @endif
</div>
