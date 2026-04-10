<?php

namespace App\Livewire\Deals;

use App\Enums\DealStage;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Deals')]
class DealList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';
    #[Url]
    public string $stageFilter = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    // Modal state
    public bool $showForm = false;
    public bool $showDeleteConfirm = false;
    public ?int $editingDealId = null;
    public ?int $deletingDealId = null;

    // Form fields
    public string $title = '';
    public string $value = '';
    public string $stage = 'Lead';
    public ?int $contactId = null;
    public ?int $companyId = null;
    public string $expectedCloseDate = '';
    public string $notes = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStageFilter(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $deal = Deal::findOrFail($id);
        $this->authorize('update', $deal);

        $this->editingDealId = $deal->id;
        $this->title = $deal->title;
        $this->value = $deal->value;
        $this->stage = $deal->stage->value;
        $this->contactId = $deal->contact_id;
        $this->companyId = $deal->company_id;
        $this->expectedCloseDate = $deal->expected_close_date?->format('Y-m-d') ?? '';
        $this->notes = $deal->notes ?? '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'stage' => 'required|string',
            'contactId' => 'required|exists:contacts,id',
            'companyId' => 'nullable|exists:companies,id',
            'expectedCloseDate' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $dealStage = DealStage::from($this->stage);

        $data = [
            'title' => $this->title,
            'value' => $this->value,
            'stage' => $dealStage,
            'contact_id' => $this->contactId,
            'company_id' => $this->companyId,
            'expected_close_date' => $this->expectedCloseDate ?: null,
            'notes' => $this->notes ?: null,
        ];

        // Auto-set closed_at for Won/Lost
        if (in_array($dealStage, [DealStage::Won, DealStage::Lost])) {
            $data['closed_at'] = now();
        } else {
            $data['closed_at'] = null;
        }

        if ($this->editingDealId) {
            $deal = Deal::findOrFail($this->editingDealId);
            $this->authorize('update', $deal);
            $deal->update($data);
        } else {
            Deal::create(['user_id' => auth()->id(), ...$data]);
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', $this->editingDealId ? 'Deal updated.' : 'Deal created.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingDealId = $id;
        $this->showDeleteConfirm = true;
    }

    public function delete(): void
    {
        $deal = Deal::findOrFail($this->deletingDealId);
        $this->authorize('delete', $deal);
        $deal->delete();

        $this->showDeleteConfirm = false;
        $this->deletingDealId = null;
        session()->flash('success', 'Deal deleted.');
    }

    public function render(): View
    {
        $deals = Deal::where('user_id', auth()->id())
            ->with(['contact', 'company'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('title', 'ilike', "%{$this->search}%")
                  ->orWhereHas('contact', fn ($c) => $c->where('first_name', 'ilike', "%{$this->search}%")
                      ->orWhere('last_name', 'ilike', "%{$this->search}%"))
                  ->orWhereHas('company', fn ($c) => $c->where('name', 'ilike', "%{$this->search}%"));
            }))
            ->when($this->stageFilter, fn ($q) => $q->where('stage', $this->stageFilter))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(25);

        return view('livewire.deals.deal-list', [
            'deals' => $deals,
            'stages' => DealStage::cases(),
            'contacts' => Contact::where('user_id', auth()->id())->orderByRaw("first_name || ' ' || last_name")->get(),
            'companies' => Company::where('user_id', auth()->id())->orderBy('name')->get(),
        ]);
    }

    private function resetForm(): void
    {
        $this->editingDealId = null;
        $this->title = '';
        $this->value = '';
        $this->stage = 'Lead';
        $this->contactId = null;
        $this->companyId = null;
        $this->expectedCloseDate = '';
        $this->notes = '';
        $this->resetValidation();
    }
}
