<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<button wire:click="logout" class="w-full text-start">
    <x-dropdown-link>
        {{ __('Log Out') }}
    </x-dropdown-link>
</button>
