<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Contacts extends Page
{
    public function url(): string
    {
        return '/contacts';
    }

    public function assert(Browser $browser): void
    {
        $browser->waitForLocation($this->url())
            ->waitForText('Contacts');
    }

    /**
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@newContact' => 'main button:contains("New Contact")',
            '@search' => 'input[placeholder="Search contacts..."]',
            '@firstName' => '#firstName',
            '@lastName' => '#lastName',
            '@email' => '#email',
            '@phone' => '#phone',
            '@title' => '#title',
            '@notes' => '#notes',
            '@modal' => '.fixed.inset-0.z-50',
        ];
    }
}
