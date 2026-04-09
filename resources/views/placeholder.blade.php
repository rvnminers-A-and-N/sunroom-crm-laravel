<x-app-layout>
    <x-slot name="header">{{ $title }}</x-slot>

    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-cream flex items-center justify-center">
            <svg class="w-8 h-8 text-brand-orange" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.1-3.36a1.5 1.5 0 010-2.56l5.1-3.36a1.5 1.5 0 012.16 1.28v6.72a1.5 1.5 0 01-2.16 1.28z" />
            </svg>
        </div>
        <h2 class="text-xl font-semibold text-text-primary mb-2">{{ $title }}</h2>
        <p class="text-gray-500">This feature is coming soon.</p>
    </div>
</x-app-layout>
