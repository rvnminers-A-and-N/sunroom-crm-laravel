<?php

namespace App\Livewire\Contacts;

use App\Models\Contact;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ContactDetail extends Component
{
    public Contact $contact;

    public function mount(int $id): void
    {
        $this->contact = Contact::with(['company', 'tags', 'deals.company', 'activities.user'])
            ->findOrFail($id);
        $this->authorize('view', $this->contact);
    }

    public function render(): View
    {
        return view('livewire.contacts.contact-detail')
            ->title($this->contact->full_name);
    }
}
