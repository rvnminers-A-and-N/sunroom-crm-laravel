<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sunroom CRM') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-light-bg flex">
            {{-- Mobile overlay --}}
            <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
                 x-transition:enter="transition-opacity ease-linear duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

            {{-- Sidebar --}}
            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                   class="fixed lg:translate-x-0 lg:static inset-y-0 left-0 z-50 w-60 bg-white border-r border-gray-200 flex flex-col transition-transform duration-200 ease-in-out">

                {{-- Logo --}}
                <div class="h-16 flex items-center px-6 border-b border-gray-200">
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-coral via-brand-orange to-gold flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-text-primary">Sunroom</span>
                    </a>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                    <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="squares-2x2" wire:navigate>
                        Dashboard
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('contacts.index')" :active="request()->routeIs('contacts.*')" icon="users" wire:navigate>
                        Contacts
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('companies.index')" :active="request()->routeIs('companies.*')" icon="building-office" wire:navigate>
                        Companies
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('deals.index')" :active="request()->routeIs('deals.*')" icon="banknotes" wire:navigate>
                        Deals
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('activities.index')" :active="request()->routeIs('activities.*')" icon="clipboard-document-list" wire:navigate>
                        Activities
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('ai.index')" :active="request()->routeIs('ai.*')" icon="sparkles" wire:navigate>
                        AI Assistant
                    </x-sidebar-link>

                    <div class="pt-4 mt-4 border-t border-gray-200">
                        <x-sidebar-link :href="route('settings')" :active="request()->routeIs('settings*')" icon="cog-6-tooth" wire:navigate>
                            Settings
                        </x-sidebar-link>
                        @if(auth()->user()->isAdmin())
                            <x-sidebar-link :href="route('admin.users')" :active="request()->routeIs('admin.*')" icon="shield-check" wire:navigate>
                                Users
                            </x-sidebar-link>
                        @endif
                    </div>
                </nav>

                {{-- User info --}}
                <div class="p-4 border-t border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-emerald-500 flex items-center justify-center text-white text-sm font-semibold">
                            {{ collect(explode(' ', auth()->user()->name))->map(fn($w) => strtoupper($w[0]))->implode('') }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-text-primary truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Main content area --}}
            <div class="flex-1 flex flex-col min-w-0">
                {{-- Toolbar --}}
                <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 lg:px-6 shrink-0">
                    {{-- Mobile hamburger --}}
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    {{-- Page title --}}
                    <h1 class="text-lg font-semibold text-text-primary hidden lg:block">
                        @if (isset($header))
                            {{ $header }}
                        @endif
                    </h1>

                    <div class="flex-1 lg:hidden"></div>

                    {{-- Right side --}}
                    <div class="flex items-center gap-3">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 transition">
                                    <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('settings')" wire:navigate>
                                    Settings
                                </x-dropdown-link>
                                <livewire:layout.navigation />
                            </x-slot>
                        </x-dropdown>
                    </div>
                </header>

                {{-- Page content --}}
                <main class="flex-1 p-4 lg:p-6 overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
