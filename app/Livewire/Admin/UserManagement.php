<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('User Management')]
class UserManagement extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    // Modal state
    public bool $showForm = false;
    public bool $showDeleteConfirm = false;
    public ?int $editingUserId = null;
    public ?int $deletingUserId = null;

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'User';

    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role->value;
        $this->password = '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->editingUserId)],
            'role' => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
        ];

        if (! $this->editingUserId) {
            $rules['password'] = ['required', 'string', 'min:8'];
        } elseif ($this->password !== '') {
            $rules['password'] = ['string', 'min:8'];
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => UserRole::from($this->role),
        ];

        if ($this->password !== '') {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingUserId) {
            User::findOrFail($this->editingUserId)->update($data);
        } else {
            User::create($data);
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', $this->editingUserId ? 'User updated.' : 'User created.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingUserId = $id;
        $this->showDeleteConfirm = true;
    }

    public function delete(): void
    {
        if ($this->deletingUserId === auth()->id()) {
            session()->flash('error', "You can't delete your own account.");
            $this->showDeleteConfirm = false;
            $this->deletingUserId = null;
            return;
        }

        User::findOrFail($this->deletingUserId)->delete();
        $this->showDeleteConfirm = false;
        $this->deletingUserId = null;
        session()->flash('success', 'User deleted.');
    }

    public function render(): View
    {
        $users = User::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'ilike', "%{$this->search}%")
                  ->orWhere('email', 'ilike', "%{$this->search}%");
            }))
            ->withCount(['contacts', 'deals'])
            ->orderBy('name')
            ->paginate(25);

        return view('livewire.admin.user-management', [
            'users' => $users,
            'roles' => UserRole::cases(),
        ]);
    }

    private function resetForm(): void
    {
        $this->editingUserId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'User';
        $this->resetValidation();
    }
}
