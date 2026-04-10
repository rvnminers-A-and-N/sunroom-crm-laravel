<?php

use App\Livewire\Activities\ActivityList;
use App\Livewire\AiAssistant;
use App\Livewire\Companies\CompanyDetail;
use App\Livewire\Companies\CompanyList;
use App\Livewire\Contacts\ContactDetail;
use App\Livewire\Contacts\ContactList;
use App\Livewire\Dashboard;
use App\Livewire\Deals\DealDetail;
use App\Livewire\Deals\DealList;
use App\Livewire\Deals\DealPipeline;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    // Contacts
    Route::get('contacts', ContactList::class)->name('contacts.index');
    Route::get('contacts/{id}', ContactDetail::class)->name('contacts.show');
    Route::get('companies', CompanyList::class)->name('companies.index');
    Route::get('companies/{id}', CompanyDetail::class)->name('companies.show');
    Route::get('deals', DealList::class)->name('deals.index');
    Route::get('deals/pipeline', DealPipeline::class)->name('deals.pipeline');
    Route::get('deals/{id}', DealDetail::class)->name('deals.show');
    Route::get('activities', ActivityList::class)->name('activities.index');
    Route::get('ai', AiAssistant::class)->name('ai.index');
    Route::view('settings', 'placeholder', ['title' => 'Settings'])->name('settings');
});

Route::middleware(['auth', 'verified', 'role:Admin'])->group(function () {
    Route::view('admin/users', 'placeholder', ['title' => 'User Management'])->name('admin.users');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
