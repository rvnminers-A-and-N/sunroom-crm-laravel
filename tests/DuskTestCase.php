<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     *
     * If a `DUSK_DRIVER_URL` is set in the environment, we assume an
     * external Chromedriver is already listening (typical local setup
     * where the system Chromedriver matches the system Chromium build).
     * Otherwise we fall back to the Chromedriver that ships with the
     * Dusk package.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (static::runningInSail()) {
            return;
        }

        if (! empty($_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL'))) {
            return;
        }

        static::startChromeDriver(['--port=9515']);
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
            '--no-sandbox',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        // When using the system Chromedriver against system Chromium, we
        // need to tell Chrome where its binary lives - the default
        // `google-chrome` binary does not exist on this Ubuntu install.
        if ($binary = $_ENV['DUSK_CHROME_BINARY'] ?? env('DUSK_CHROME_BINARY')) {
            $options->setBinary($binary);
        }

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
