<?php

namespace App\Livewire;

use App\Models\Activity;
use App\Models\Contact;
use App\Services\OllamaService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('AI Assistant')]
class AiAssistant extends Component
{
    public string $question = '';
    public array $messages = [];
    public bool $loading = false;

    public function ask(OllamaService $ollama): void
    {
        $this->validate([
            'question' => 'required|string|max:500',
        ]);

        $userQuestion = $this->question;
        $this->messages[] = ['role' => 'user', 'content' => $userQuestion];
        $this->question = '';
        $this->loading = true;

        // Build context from user's CRM data
        $userId = auth()->id();
        $contactCount = Contact::where('user_id', $userId)->count();
        $recentContacts = Contact::where('user_id', $userId)
            ->with('company')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($c) => "- {$c->first_name} {$c->last_name}".($c->company ? " at {$c->company->name}" : ''))
            ->implode("\n");

        $recentActivities = Activity::where('user_id', $userId)
            ->with('contact')
            ->orderByDesc('occurred_at')
            ->limit(20)
            ->get()
            ->map(fn ($a) => "- [{$a->type->value}] {$a->subject}".($a->contact ? " (with {$a->contact->first_name} {$a->contact->last_name})" : ''))
            ->implode("\n");

        $context = "You have {$contactCount} contacts.\n\nRecent contacts:\n{$recentContacts}\n\nRecent activities:\n{$recentActivities}";

        $answer = $ollama->ask($userQuestion, $context);

        $this->messages[] = ['role' => 'assistant', 'content' => $answer];
        $this->loading = false;
    }

    public function clearChat(): void
    {
        $this->messages = [];
    }

    public function render(): View
    {
        return view('livewire.ai-assistant', [
            'aiEnabled' => app(OllamaService::class)->isEnabled(),
        ]);
    }
}
