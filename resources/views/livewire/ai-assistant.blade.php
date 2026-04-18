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

        {{-- Streaming response --}}
        <div x-data="{ streamingText: '' }" x-show="streamingText" x-cloak class="px-6 pb-4">
            <div class="flex gap-3">
                <div class="shrink-0">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-coral via-brand-orange to-gold flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>
                    </div>
                </div>
                <div class="flex-1 max-w-2xl">
                    <div class="inline-block px-4 py-3 rounded-2xl bg-gray-100 text-text-primary text-sm whitespace-pre-wrap" x-text="streamingText"></div>
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div class="border-t border-gray-200 p-4"
             x-data="{
                 async submitQuestion() {
                     const result = await $wire.prepareAsk();
                     if (!result) return;

                     let fullText = '';
                     const parentEl = $el.closest('.flex.flex-col');
                     const streamEl = parentEl.querySelector('[x-data]');
                     const streamScope = Alpine.$data(streamEl);
                     streamScope.streamingText = '';

                     try {
                         const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
                         const resp = await fetch('/ai/ask/stream', {
                             method: 'POST',
                             credentials: 'same-origin',
                             headers: {
                                 'Content-Type': 'application/json',
                                 'X-CSRF-TOKEN': csrfToken || '',
                                 'Accept': 'text/event-stream',
                             },
                             body: JSON.stringify(result),
                         });

                         const reader = resp.body.getReader();
                         const decoder = new TextDecoder();
                         let buffer = '';

                         while (true) {
                             const { done, value } = await reader.read();
                             if (done) break;
                             buffer += decoder.decode(value, { stream: true });
                             const lines = buffer.split('\\n');
                             buffer = lines.pop();
                             for (const line of lines) {
                                 const trimmed = line.trim();
                                 if (!trimmed || !trimmed.startsWith('data: ')) continue;
                                 const payload = trimmed.slice(6);
                                 if (payload === '[DONE]') break;
                                 try {
                                     const parsed = JSON.parse(payload);
                                     if (parsed.token) {
                                         fullText += parsed.token;
                                         streamScope.streamingText = fullText;
                                     }
                                 } catch (e) {}
                             }
                         }
                     } catch (e) {
                         fullText = fullText || 'AI service is currently unavailable.';
                     }

                     streamScope.streamingText = '';
                     await $wire.finishAsk(fullText);
                 }
             }">
            <form @submit.prevent="submitQuestion" class="flex gap-2">
                <input wire:model="question" type="text"
                       placeholder="Ask anything about your CRM..."
                       :disabled="$wire.loading"
                       class="flex-1 border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <x-primary-button type="submit" :disabled="$wire.loading">
                    <span x-show="!$wire.loading" class="flex items-center gap-1.5">
                        Send
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                        </svg>
                    </span>
                    <span x-show="$wire.loading" x-cloak>Thinking...</span>
                </x-primary-button>
            </form>
            @error('question') <p class="text-xs text-coral mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Smart Search Section --}}
    <div class="bg-white rounded-xl border border-gray-200 mt-6">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-text-primary flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                Smart Search
            </h3>
            <p class="text-sm text-gray-500 mt-0.5">Search your CRM data with natural language queries</p>
        </div>

        {{-- Search streaming response --}}
        <div x-data="{ searchStreamingText: '' }" x-show="searchStreamingText" x-cloak class="px-6 pt-4">
            <div class="flex gap-3">
                <div class="shrink-0">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-coral via-brand-orange to-gold flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>
                    </div>
                </div>
                <div class="flex-1 max-w-2xl">
                    <div class="inline-block px-4 py-3 rounded-2xl bg-gray-100 text-text-primary text-sm whitespace-pre-wrap" x-text="searchStreamingText"></div>
                </div>
            </div>
        </div>

        {{-- Search result display --}}
        @if($searchResult)
            <div class="px-6 pt-4">
                <div class="flex gap-3">
                    <div class="shrink-0">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-coral via-brand-orange to-gold flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>
                        </div>
                    </div>
                    <div class="flex-1 max-w-2xl">
                        <div class="inline-block px-4 py-3 rounded-2xl bg-gray-100 text-text-primary text-sm whitespace-pre-wrap">{{ $searchResult }}</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Search input --}}
        <div class="p-4"
             x-data="{
                 async submitSearch() {
                     const result = await $wire.prepareSearch();
                     if (!result) return;

                     let fullText = '';
                     const parentEl = $el.closest('.bg-white');
                     const streamEl = parentEl.querySelector('[x-data]');
                     const streamScope = Alpine.$data(streamEl);
                     streamScope.searchStreamingText = '';

                     try {
                         const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
                         const resp = await fetch('/ai/search/stream', {
                             method: 'POST',
                             credentials: 'same-origin',
                             headers: {
                                 'Content-Type': 'application/json',
                                 'X-CSRF-TOKEN': csrfToken || '',
                                 'Accept': 'text/event-stream',
                             },
                             body: JSON.stringify(result),
                         });

                         const reader = resp.body.getReader();
                         const decoder = new TextDecoder();
                         let buffer = '';

                         while (true) {
                             const { done, value } = await reader.read();
                             if (done) break;
                             buffer += decoder.decode(value, { stream: true });
                             const lines = buffer.split('\\n');
                             buffer = lines.pop();
                             for (const line of lines) {
                                 const trimmed = line.trim();
                                 if (!trimmed || !trimmed.startsWith('data: ')) continue;
                                 const payload = trimmed.slice(6);
                                 if (payload === '[DONE]') break;
                                 try {
                                     const parsed = JSON.parse(payload);
                                     if (parsed.token) {
                                         fullText += parsed.token;
                                         streamScope.searchStreamingText = fullText;
                                     }
                                 } catch (e) {}
                             }
                         }
                     } catch (e) {
                         fullText = fullText || 'AI service is currently unavailable.';
                     }

                     streamScope.searchStreamingText = '';
                     await $wire.finishSearch(fullText);
                 }
             }">
            <form @submit.prevent="submitSearch" class="flex gap-2">
                <input wire:model="searchQuery" type="text"
                       placeholder="Search your CRM with natural language..."
                       :disabled="$wire.searchLoading"
                       class="flex-1 border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <x-primary-button type="submit" :disabled="$wire.searchLoading">
                    <span x-show="!$wire.searchLoading" class="flex items-center gap-1.5">
                        Search
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </span>
                    <span x-show="$wire.searchLoading" x-cloak>Searching...</span>
                </x-primary-button>
            </form>
            @error('searchQuery') <p class="text-xs text-coral mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Deal Insights Section --}}
    <div class="bg-white rounded-xl border border-gray-200 mt-6">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-text-primary flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
                </svg>
                Deal Insights
            </h3>
            <p class="text-sm text-gray-500 mt-0.5">Generate AI-powered insights for a specific deal</p>
        </div>

        {{-- Insights streaming response --}}
        <div x-data="{ insightsStreamingText: '' }" x-show="insightsStreamingText" x-cloak class="px-6 pt-4">
            <div class="flex gap-3">
                <div class="shrink-0">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-coral via-brand-orange to-gold flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>
                    </div>
                </div>
                <div class="flex-1 max-w-2xl">
                    <div class="inline-block px-4 py-3 rounded-2xl bg-gray-100 text-text-primary text-sm whitespace-pre-wrap" x-text="insightsStreamingText"></div>
                </div>
            </div>
        </div>

        {{-- Insights result display --}}
        @if($insightsResult)
            <div class="px-6 pt-4">
                <div class="flex gap-3">
                    <div class="shrink-0">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-coral via-brand-orange to-gold flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>
                        </div>
                    </div>
                    <div class="flex-1 max-w-2xl">
                        <div class="inline-block px-4 py-3 rounded-2xl bg-gray-100 text-text-primary text-sm whitespace-pre-wrap">{{ $insightsResult }}</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Insights input --}}
        <div class="p-4"
             x-data="{
                 async submitInsights() {
                     const result = await $wire.prepareDealInsights();
                     if (!result) return;

                     let fullText = '';
                     const parentEl = $el.closest('.bg-white');
                     const streamEl = parentEl.querySelector('[x-data]');
                     const streamScope = Alpine.$data(streamEl);
                     streamScope.insightsStreamingText = '';

                     try {
                         const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
                         const resp = await fetch('/ai/deal-insights/' + result.dealId + '/stream', {
                             method: 'POST',
                             credentials: 'same-origin',
                             headers: {
                                 'Content-Type': 'application/json',
                                 'X-CSRF-TOKEN': csrfToken || '',
                                 'Accept': 'text/event-stream',
                             },
                             body: JSON.stringify({}),
                         });

                         const reader = resp.body.getReader();
                         const decoder = new TextDecoder();
                         let buffer = '';

                         while (true) {
                             const { done, value } = await reader.read();
                             if (done) break;
                             buffer += decoder.decode(value, { stream: true });
                             const lines = buffer.split('\\n');
                             buffer = lines.pop();
                             for (const line of lines) {
                                 const trimmed = line.trim();
                                 if (!trimmed || !trimmed.startsWith('data: ')) continue;
                                 const payload = trimmed.slice(6);
                                 if (payload === '[DONE]') break;
                                 try {
                                     const parsed = JSON.parse(payload);
                                     if (parsed.token) {
                                         fullText += parsed.token;
                                         streamScope.insightsStreamingText = fullText;
                                     }
                                 } catch (e) {}
                             }
                         }
                     } catch (e) {
                         fullText = fullText || 'AI service is currently unavailable.';
                     }

                     streamScope.insightsStreamingText = '';
                     await $wire.finishDealInsights(fullText);
                 }
             }">
            <form @submit.prevent="submitInsights" class="flex gap-2">
                <input wire:model="insightsDealId" type="number" min="1"
                       placeholder="Enter Deal ID..."
                       :disabled="$wire.insightsLoading"
                       class="w-48 border-gray-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <x-primary-button type="submit" :disabled="$wire.insightsLoading">
                    <span x-show="!$wire.insightsLoading" class="flex items-center gap-1.5">
                        Generate Insights
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
                        </svg>
                    </span>
                    <span x-show="$wire.insightsLoading" x-cloak>Generating...</span>
                </x-primary-button>
            </form>
            @error('insightsDealId') <p class="text-xs text-coral mt-1">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
