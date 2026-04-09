@props(['label', 'value', 'icon', 'color' => 'emerald-500'])

<div class="bg-white rounded-xl border border-gray-200 p-6">
    <div class="flex items-center gap-4">
        <div class="p-3 rounded-lg bg-{{ $color }}/10 shrink-0">
            {{ $icon }}
        </div>
        <div class="min-w-0">
            <p class="text-sm text-gray-500">{{ $label }}</p>
            <p class="text-2xl font-bold text-text-primary truncate">{{ $value }}</p>
        </div>
    </div>
</div>
