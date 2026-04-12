<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class DealPipeline extends Page
{
    public function url(): string
    {
        return '/deals/pipeline';
    }

    public function assert(Browser $browser): void
    {
        $browser->waitForLocation($this->url())
            ->waitForText('Deal Pipeline');
    }

    /**
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@leadColumn' => '.pipeline-column[data-stage="Lead"]',
            '@qualifiedColumn' => '.pipeline-column[data-stage="Qualified"]',
            '@wonColumn' => '.pipeline-column[data-stage="Won"]',
        ];
    }

    public function dealCard(int $dealId): string
    {
        return ".pipeline-card[data-deal-id=\"{$dealId}\"]";
    }

    public function stageColumn(string $stage): string
    {
        return ".pipeline-column[data-stage=\"{$stage}\"]";
    }
}
