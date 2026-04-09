@props(['show' => false, 'title' => 'Confirm', 'message' => 'Are you sure?'])

<div x-data="{ open: @entangle($attributes->wire('model')).live }"
     x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center">
    {{-- Backdrop --}}
    <div x-show="open" @click="open = false"
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50"></div>

    {{-- Dialog --}}
    <div x-show="open"
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold text-text-primary">{{ $title }}</h3>
        <p class="text-sm text-gray-600 mt-2">{{ $message }}</p>
        <div class="flex justify-end gap-3 mt-6">
            <x-secondary-button @click="open = false">Cancel</x-secondary-button>
            {{ $slot }}
        </div>
    </div>
</div>
