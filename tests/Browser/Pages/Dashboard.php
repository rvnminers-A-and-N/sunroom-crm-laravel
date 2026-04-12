<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Dashboard extends Page
{
    public function url(): string
    {
        return '/dashboard';
    }

    public function assert(Browser $browser): void
    {
        $browser->waitForLocation($this->url())
            ->waitForText('Total Contacts');
    }

    /**
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@totalContacts' => 'main',
            '@sidebarContacts' => 'aside a[href$="/contacts"]',
            '@sidebarDeals' => 'aside a[href$="/deals"]',
            '@sidebarPipeline' => 'aside a[href*="deals"]',
            '@sidebarSettings' => 'aside a[href$="/settings"]',
        ];
    }
}
