<?php

use App\View\Components\GuestLayout;
use Illuminate\View\View;

it('returns the layouts.guest view from render()', function () {
    $component = new GuestLayout;
    $view = $component->render();

    expect($view)->toBeInstanceOf(View::class)
        ->and($view->name())->toBe('layouts.guest');
});

it('renders the guest layout for the login screen', function () {
    $this->get('/login')->assertOk();
});
