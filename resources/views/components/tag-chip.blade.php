@props(['color' => '#02795F', 'name'])

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white"
      style="background-color: {{ $color }}">
    {{ $name }}
</span>
