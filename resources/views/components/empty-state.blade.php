@props(['title', 'message' => null])

<div class="text-center py-12">
    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-cream flex items-center justify-center">
        {{ $icon ?? '' }}
    </div>
    <h3 class="text-lg font-semibold text-text-primary">{{ $title }}</h3>
    @if($message)
        <p class="text-sm text-gray-500 mt-1">{{ $message }}</p>
    @endif
    @if(isset($action))
        <div class="mt-4">
            {{ $action }}
        </div>
    @endif
</div>
