<?php

namespace App\Livewire;

use App\Models\Tag;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Settings')]
class Settings extends Component
{
    #[Url]
    public string $tab = 'profile';

    // Profile fields
    public string $name = '';
    public string $email = '';

    // Password fields
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPasswordConfirmation = '';

    // Tag form fields
    public bool $showTagForm = false;
    public bool $showDeleteConfirm = false;
    public ?int $editingTagId = null;
    public ?int $deletingTagId = null;
    public string $tagName = '';
    public string $tagColor = '#02795F';

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function updateProfile(): void
    {
        $user = auth()->user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        session()->flash('success', 'Profile updated.');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'currentPassword' => ['required', 'current_password'],
            'newPassword' => ['required', 'string', 'min:8', 'same:newPasswordConfirmation'],
        ], messages: [
            'newPassword.same' => 'The new password and confirmation must match.',
        ], attributes: [
            'currentPassword' => 'current password',
            'newPassword' => 'new password',
        ]);

        auth()->user()->update([
            'password' => Hash::make($this->newPassword),
        ]);

        $this->reset(['currentPassword', 'newPassword', 'newPasswordConfirmation']);
        session()->flash('success', 'Password updated.');
    }

    // Tag actions
    public function createTag(): void
    {
        $this->resetTagForm();
        $this->showTagForm = true;
    }

    public function editTag(int $id): void
    {
        $tag = Tag::findOrFail($id);
        $this->editingTagId = $tag->id;
        $this->tagName = $tag->name;
        $this->tagColor = $tag->color;
        $this->showTagForm = true;
    }

    public function saveTag(): void
    {
        $rules = [
            'tagName' => ['required', 'string', 'max:50', Rule::unique('tags', 'name')->ignore($this->editingTagId)],
            'tagColor' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];

        $this->validate($rules, attributes: [
            'tagName' => 'name',
            'tagColor' => 'color',
        ]);

        if ($this->editingTagId) {
            Tag::findOrFail($this->editingTagId)->update([
                'name' => $this->tagName,
                'color' => $this->tagColor,
            ]);
        } else {
            Tag::create([
                'name' => $this->tagName,
                'color' => $this->tagColor,
            ]);
        }

        $this->showTagForm = false;
        $this->resetTagForm();
        session()->flash('success', $this->editingTagId ? 'Tag updated.' : 'Tag created.');
    }

    public function confirmDeleteTag(int $id): void
    {
        $this->deletingTagId = $id;
        $this->showDeleteConfirm = true;
    }

    public function deleteTag(): void
    {
        Tag::findOrFail($this->deletingTagId)->delete();
        $this->showDeleteConfirm = false;
        $this->deletingTagId = null;
        session()->flash('success', 'Tag deleted.');
    }

    public function render(): View
    {
        return view('livewire.settings', [
            'tags' => Tag::withCount('contacts')->orderBy('name')->get(),
        ]);
    }

    private function resetTagForm(): void
    {
        $this->editingTagId = null;
        $this->tagName = '';
        $this->tagColor = '#02795F';
        $this->resetValidation();
    }
}
