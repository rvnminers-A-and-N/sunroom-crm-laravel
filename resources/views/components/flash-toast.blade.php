@php
    $success = session('success');
    $error = session('error');
@endphp

@if($success || $error)
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 4000)"
         x-transition:enter="transform ease-out duration-300"
         x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
         x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed top-20 right-4 z-[100] max-w-sm w-full pointer-events-auto">
        <div class="rounded-xl shadow-lg ring-1 ring-black/5 overflow-hidden {{ $success ? 'bg-white' : 'bg-white' }}">
            <div class="p-4 flex items-start gap-3">
                @if($success)
                    <div class="shrink-0 w-8 h-8 rounded-full bg-emerald-500/15 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0 pt-0.5">
                        <p class="text-sm font-medium text-text-primary">{{ $success }}</p>
                    </div>
                @else
                    <div class="shrink-0 w-8 h-8 rounded-full bg-coral/15 flex items-center justify-center">
                        <svg class="w-5 h-5 text-coral" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0 pt-0.5">
                        <p class="text-sm font-medium text-text-primary">{{ $error }}</p>
                    </div>
                @endif
                <button @click="show = false" class="shrink-0 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
@endif
