<?php

namespace App\Livewire\Companies;

use App\Models\Company;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CompanyDetail extends Component
{
    public Company $company;

    public function mount(int $id): void
    {
        $this->company = Company::with(['contacts.tags', 'deals.contact'])
            ->withCount(['contacts', 'deals'])
            ->findOrFail($id);
        $this->authorize('view', $this->company);
    }

    public function render(): View
    {
        return view('livewire.companies.company-detail')
            ->title($this->company->name);
    }
}
