@props(['title', 'subtitle' => null])

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-text-primary">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-sm text-gray-500 mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions))
        <div class="flex items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
