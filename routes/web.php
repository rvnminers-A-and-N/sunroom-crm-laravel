<?php

use App\Livewire\Contacts\ContactDetail;
use App\Livewire\Contacts\ContactList;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    // Contacts
    Route::get('contacts', ContactList::class)->name('contacts.index');
    Route::get('contacts/{id}', ContactDetail::class)->name('contacts.show');
    Route::view('companies', 'placeholder', ['title' => 'Companies'])->name('companies.index');
    Route::view('companies/{id}', 'placeholder', ['title' => 'Company Detail'])->name('companies.show');
    Route::view('deals', 'placeholder', ['title' => 'Deals'])->name('deals.index');
    Route::view('deals/{id}', 'placeholder', ['title' => 'Deal Detail'])->name('deals.show');
    Route::view('activities', 'placeholder', ['title' => 'Activities'])->name('activities.index');
    Route::view('ai', 'placeholder', ['title' => 'AI Assistant'])->name('ai.index');
    Route::view('settings', 'placeholder', ['title' => 'Settings'])->name('settings');
});

Route::middleware(['auth', 'verified', 'role:Admin'])->group(function () {
    Route::view('admin/users', 'placeholder', ['title' => 'User Management'])->name('admin.users');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
