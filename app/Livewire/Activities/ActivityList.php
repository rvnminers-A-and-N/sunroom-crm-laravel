<?php

namespace App\Livewire\Activities;

use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Contact;
use App\Models\Deal;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Activities')]
class ActivityList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';
    #[Url]
    public string $typeFilter = '';
    public ?int $contactFilter = null;
    public ?int $dealFilter = null;

    // Modal state
    public bool $showForm = false;
    public bool $showDeleteConfirm = false;
    public ?int $editingActivityId = null;
    public ?int $deletingActivityId = null;

    // Form fields
    public string $type = 'Note';
    public string $subject = '';
    public string $body = '';
    public string $occurredAt = '';
    public ?int $contactId = null;
    public ?int $dealId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingContactFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDealFilter(): void
    {
        $this->resetPage();
    }

    public function setTypeFilter(string $type): void
    {
        $this->typeFilter = $type;
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->occurredAt = now()->format('Y-m-d\TH:i');
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $activity = Activity::findOrFail($id);
        $this->authorize('update', $activity);

        $this->editingActivityId = $activity->id;
        $this->type = $activity->type->value;
        $this->subject = $activity->subject;
        $this->body = $activity->body ?? '';
        $this->occurredAt = $activity->occurred_at->format('Y-m-d\TH:i');
        $this->contactId = $activity->contact_id;
        $this->dealId = $activity->deal_id;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'type' => 'required|string',
            'subject' => 'required|string|max:255',
            'body' => 'nullable|string',
            'occurredAt' => 'required|date',
            'contactId' => 'nullable|exists:contacts,id',
            'dealId' => 'nullable|exists:deals,id',
        ]);

        $data = [
            'type' => ActivityType::from($this->type),
            'subject' => $this->subject,
            'body' => $this->body ?: null,
            'occurred_at' => $this->occurredAt,
            'contact_id' => $this->contactId,
            'deal_id' => $this->dealId,
        ];

        if ($this->editingActivityId) {
            $activity = Activity::findOrFail($this->editingActivityId);
            $this->authorize('update', $activity);
            $activity->update($data);
        } else {
            Activity::create(['user_id' => auth()->id(), ...$data]);
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', $this->editingActivityId ? 'Activity updated.' : 'Activity logged.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingActivityId = $id;
        $this->showDeleteConfirm = true;
    }

    public function delete(): void
    {
        $activity = Activity::findOrFail($this->deletingActivityId);
        $this->authorize('delete', $activity);
        $activity->delete();

        $this->showDeleteConfirm = false;
        $this->deletingActivityId = null;
        session()->flash('success', 'Activity deleted.');
    }

    public function render(): View
    {
        $activities = Activity::where('user_id', auth()->id())
            ->with(['contact', 'deal', 'user'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('subject', 'ilike', "%{$this->search}%")
                  ->orWhere('body', 'ilike', "%{$this->search}%");
            }))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->contactFilter, fn ($q) => $q->where('contact_id', $this->contactFilter))
            ->when($this->dealFilter, fn ($q) => $q->where('deal_id', $this->dealFilter))
            ->orderByDesc('occurred_at')
            ->paginate(25);

        return view('livewire.activities.activity-list', [
            'activities' => $activities,
            'types' => ActivityType::cases(),
            'contacts' => Contact::where('user_id', auth()->id())->orderByRaw("first_name || ' ' || last_name")->get(),
            'deals' => Deal::where('user_id', auth()->id())->orderBy('title')->get(),
        ]);
    }

    private function resetForm(): void
    {
        $this->editingActivityId = null;
        $this->type = 'Note';
        $this->subject = '';
        $this->body = '';
        $this->occurredAt = '';
        $this->contactId = null;
        $this->dealId = null;
        $this->resetValidation();
    }
}
