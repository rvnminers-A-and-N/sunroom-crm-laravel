<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-coral border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-coral/90 active:bg-coral focus:outline-none focus:ring-2 focus:ring-coral focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
