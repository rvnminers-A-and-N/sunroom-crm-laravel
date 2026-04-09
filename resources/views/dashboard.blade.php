<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-gray-600">Welcome back, {{ auth()->user()->name }}! Dashboard stats coming in the next branch.</p>
    </div>
</x-app-layout>
