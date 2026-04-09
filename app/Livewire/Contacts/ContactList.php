<?php

namespace App\Livewire\Contacts;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Contacts')]
class ContactList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';
    public ?int $companyFilter = null;
    public ?int $tagFilter = null;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    // Modal state
    public bool $showForm = false;
    public bool $showDeleteConfirm = false;
    public ?int $editingContactId = null;
    public ?int $deletingContactId = null;

    // Form fields
    public string $firstName = '';
    public string $lastName = '';
    public string $email = '';
    public string $phone = '';
    public string $title = '';
    public string $notes = '';
    public ?int $companyId = null;
    public array $tagIds = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCompanyFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTagFilter(): void
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
        $contact = Contact::with('tags')->findOrFail($id);
        $this->authorize('update', $contact);

        $this->editingContactId = $contact->id;
        $this->firstName = $contact->first_name;
        $this->lastName = $contact->last_name;
        $this->email = $contact->email ?? '';
        $this->phone = $contact->phone ?? '';
        $this->title = $contact->title ?? '';
        $this->notes = $contact->notes ?? '';
        $this->companyId = $contact->company_id;
        $this->tagIds = $contact->tags->pluck('id')->toArray();
        $this->showForm = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'firstName' => 'required|string|max:100',
            'lastName' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'companyId' => 'nullable|exists:companies,id',
            'tagIds' => 'nullable|array',
            'tagIds.*' => 'exists:tags,id',
        ]);

        $data = [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'title' => $this->title ?: null,
            'notes' => $this->notes ?: null,
            'company_id' => $this->companyId,
        ];

        if ($this->editingContactId) {
            $contact = Contact::findOrFail($this->editingContactId);
            $this->authorize('update', $contact);
            $contact->update($data);
        } else {
            $contact = Contact::create([
                'user_id' => auth()->id(),
                ...$data,
            ]);
        }

        $contact->tags()->sync($this->tagIds);

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', $this->editingContactId ? 'Contact updated.' : 'Contact created.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingContactId = $id;
        $this->showDeleteConfirm = true;
    }

    public function delete(): void
    {
        $contact = Contact::findOrFail($this->deletingContactId);
        $this->authorize('delete', $contact);
        $contact->delete();

        $this->showDeleteConfirm = false;
        $this->deletingContactId = null;
        session()->flash('success', 'Contact deleted.');
    }

    public function render(): View
    {
        $contacts = Contact::where('user_id', auth()->id())
            ->with(['company', 'tags'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('first_name', 'ilike', "%{$this->search}%")
                  ->orWhere('last_name', 'ilike', "%{$this->search}%")
                  ->orWhere('email', 'ilike', "%{$this->search}%");
            }))
            ->when($this->companyFilter, fn ($q) => $q->where('company_id', $this->companyFilter))
            ->when($this->tagFilter, fn ($q) => $q->whereHas('tags', fn ($t) => $t->where('tags.id', $this->tagFilter)))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(25);

        return view('livewire.contacts.contact-list', [
            'contacts' => $contacts,
            'companies' => Company::where('user_id', auth()->id())->orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    private function resetForm(): void
    {
        $this->editingContactId = null;
        $this->firstName = '';
        $this->lastName = '';
        $this->email = '';
        $this->phone = '';
        $this->title = '';
        $this->notes = '';
        $this->companyId = null;
        $this->tagIds = [];
        $this->resetValidation();
    }
}
