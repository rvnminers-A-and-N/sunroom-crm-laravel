<?php

namespace App\Livewire\Companies;

use App\Models\Company;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Companies')]
class CompanyList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    // Modal state
    public bool $showForm = false;

    public bool $showDeleteConfirm = false;

    public ?int $editingCompanyId = null;

    public ?int $deletingCompanyId = null;

    // Form fields
    public string $name = '';

    public string $industry = '';

    public string $website = '';

    public string $phone = '';

    public string $address = '';

    public string $city = '';

    public string $state = '';

    public string $zip = '';

    public string $notes = '';

    public function updatingSearch(): void
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
        $company = Company::findOrFail($id);
        $this->authorize('update', $company);

        $this->editingCompanyId = $company->id;
        $this->name = $company->name;
        $this->industry = $company->industry ?? '';
        $this->website = $company->website ?? '';
        $this->phone = $company->phone ?? '';
        $this->address = $company->address ?? '';
        $this->city = $company->city ?? '';
        $this->state = $company->state ?? '';
        $this->zip = $company->zip ?? '';
        $this->notes = $company->notes ?? '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'zip' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $data = [
            'name' => $this->name,
            'industry' => $this->industry ?: null,
            'website' => $this->website ?: null,
            'phone' => $this->phone ?: null,
            'address' => $this->address ?: null,
            'city' => $this->city ?: null,
            'state' => $this->state ?: null,
            'zip' => $this->zip ?: null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingCompanyId) {
            $company = Company::findOrFail($this->editingCompanyId);
            $this->authorize('update', $company);
            $company->update($data);
        } else {
            Company::create(['user_id' => auth()->id(), ...$data]);
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', $this->editingCompanyId ? 'Company updated.' : 'Company created.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingCompanyId = $id;
        $this->showDeleteConfirm = true;
    }

    public function delete(): void
    {
        $company = Company::findOrFail($this->deletingCompanyId);
        $this->authorize('delete', $company);
        $company->delete();

        $this->showDeleteConfirm = false;
        $this->deletingCompanyId = null;
        session()->flash('success', 'Company deleted.');
    }

    public function render(): View
    {
        $companies = Company::where('user_id', auth()->id())
            ->withCount(['contacts', 'deals'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'ilike', "%{$this->search}%")
                    ->orWhere('industry', 'ilike', "%{$this->search}%")
                    ->orWhere('city', 'ilike', "%{$this->search}%");
            }))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(25);

        return view('livewire.companies.company-list', [
            'companies' => $companies,
        ]);
    }

    private function resetForm(): void
    {
        $this->editingCompanyId = null;
        $this->name = '';
        $this->industry = '';
        $this->website = '';
        $this->phone = '';
        $this->address = '';
        $this->city = '';
        $this->state = '';
        $this->zip = '';
        $this->notes = '';
        $this->resetValidation();
    }
}
