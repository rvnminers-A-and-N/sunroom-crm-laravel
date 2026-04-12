<?php

use App\Models\User;
use App\View\Components\AppLayout;
use Illuminate\View\View;

it('returns the layouts.app view from render()', function () {
    $component = new AppLayout;
    $view = $component->render();

    expect($view)->toBeInstanceOf(View::class)
        ->and($view->name())->toBe('layouts.app');
});

it('renders inside an authenticated request without errors', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/dashboard')->assertOk();
});
