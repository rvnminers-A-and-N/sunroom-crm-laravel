@section('page-title', 'AI Assistant')

<div>
    <x-page-header title="AI Assistant" subtitle="Ask questions about your CRM data">
        <x-slot name="actions">
            @if(count($messages) > 0)
                <x-secondary-button wire:click="clearChat">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165" />
                    </svg>
                    Clear Chat
                </x-secondary-button>
            @endif
        </x-slot>
    </x-page-header>

    @if(! $aiEnabled)
        <div class="bg-gold/10 border border-gold/30 rounded-xl p-4 mb-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-gold shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <div class="text-sm">
                <p class="font-semibold text-text-primary">AI features are disabled</p>
                <p class="text-gray-600 mt-0.5">Set <code class="bg-gold/20 px-1.5 py-0.5 rounded text-xs">OLLAMA_ENABLED=true</code> in your <code class="bg-gold/20 px-1.5 py-0.5 rounded text-xs">.env</code> file and ensure Ollama is running locally to enable the assistant.</p>
            </div>
        </div>
    @endif

    {{-- Chat Container --}}
    <div class="bg-white rounded-xl border border-gray-200 flex flex-col" style="height: calc(100vh - 14rem);">
        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto p-6 space-y-4" x-data x-init="$watch('$wire.messages', () => $nextTick(() => $el.scrollTop = $el.scrollHeight))">
            @if(empty($messages))
                <div class="h-full flex flex-col items-center justify-center text-center">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-coral via-brand-orange to-gold flex items-center justify-center mb-4">
                        <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-text-primary mb-1">How can I help you today?</h3>
                    <p class="text-sm text-gray-500 max-w-md">Ask me anything about your contacts, deals, and activities. I can help you find information, summarize data, and suggest next steps.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-6 max-w-xl w-full">
                        <button wire:click="$set('question', 'What deals should I focus on this week?')"
                                class="text-left text-sm p-3 rounded-lg border border-gray-200 hover:border-emerald-500 hover:bg-emerald-500/5 transition-colors text-gray-600">
                            What deals should I focus on this week?
                        </button>
                        <button wire:click="$set('question', 'Summarize my recent activities')"
                                class="text-left text-sm p-3 rounded-lg border border-gray-200 hover:border-emerald-500 hover:bg-emerald-500/5 transition-colors text-gray-600">
                            Summarize my recent activities
                        </button>
                        <button wire:click="$set('question', 'Which contacts haven\'t I followed up with?')"
                                class="text-left text-sm p-3 rounded-lg border border-gray-200 hover:border-emerald-500 hover:bg-emerald-500/5 transition-colors text-gray-600">
                            Which contacts haven't I followed up with?
                        </button>
                        <button wire:click="$set('question', 'What is my pipeline value?')"
                                class="text-left text-sm p-3 rounded-lg border border-gray-200 hover:border-emerald-500 hover:bg-emerald-500/5 transition-colors text-gray-600">
                            What is my pipeline value?
                        </button>
                    </div>
                </div>
            @else
                @foreach($messages as $message)
                    <div class="flex gap-3 {{ $message['role'] === 'user' ? 'flex-row-reverse' : '' }}">
                        <div class="shrink-0">
                            @if($message['role'] === 'user')
                                <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs font-semibold">
                                    {{ collect(explode(' ', auth()->user()->name))->map(fn($w) => strtoupper($w[0]))->implode('') }}
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-coral via-brand-orange to-gold flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 max-w-2xl {{ $message['role'] === 'user' ? 'text-right' : '' }}">
                            <div class="inline-block px-4 py-3 rounded-2xl text-sm whitespace-pre-wrap text-left {{ $message['role'] === 'user' ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-text-primary' }}">{{ $message['content'] }}</div>
                        </div>
                    </div>
                @endforeach

                @if($loading)
                    <div class="flex gap-3">
                        <div class="shrink-0">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-coral via-brand-orange to-gold flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>
                            </div>
                        </div>
                        <div class="px-4 py-3 rounded-2xl bg-gray-100">
                            <div class="flex gap-1">
                                <div class="w-2 h-2 rounded-full bg-gray-400 animate-bounce" style="animation-delay: 0ms"></div>
                                <div class="w-2 h-2 rounded-full bg-gray-400 animate-bounce" style="animation-delay: 150ms"></div>
                                <div class="w-2 h-2 rounded-full bg-gray-400 animate-bounce" style="animation-delay: 300ms"></div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Input --}}
        <div class="border-t border-gray-200 p-4">
            <form wire:submit="ask" class="flex gap-2">
                <input wire:model="question" type="text"
                       placeholder="Ask anything about your CRM..."
                       wire:loading.attr="disabled" wire:target="ask"
                       class="flex-1 border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <x-primary-button type="submit" wire:loading.attr="disabled" wire:target="ask">
                    <span wire:loading.remove wire:target="ask" class="flex items-center gap-1.5">
                        Send
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                        </svg>
                    </span>
                    <span wire:loading wire:target="ask">Thinking...</span>
                </x-primary-button>
            </form>
            @error('question') <p class="text-xs text-coral mt-1">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
