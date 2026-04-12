<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Login extends Page
{
    public function url(): string
    {
        return '/login';
    }

    public function assert(Browser $browser): void
    {
        $browser->waitForLocation($this->url())
            ->waitFor('@email');
    }

    /**
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@email' => '#email',
            '@password' => '#password',
            '@remember' => '#remember',
            '@submit' => 'button[type="submit"]',
        ];
    }
}
