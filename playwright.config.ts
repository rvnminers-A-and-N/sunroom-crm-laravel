import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright cross-browser smoke configuration for Sunroom CRM.
 *
 * Dusk owns the Livewire-aware behavioural coverage (Chrome only, with first-class
 * `@wire` support). Playwright owns the "it renders + works in every browser" smoke
 * of the golden-path flow across Chromium, Firefox, and WebKit.
 */
export default defineConfig({
  testDir: './playwright/tests',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: 1,
  reporter: process.env.CI ? [['github'], ['html', { open: 'never' }]] : 'list',
  timeout: 30_000,
  expect: { timeout: 5_000 },

  use: {
    baseURL: 'http://127.0.0.1:8000',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    actionTimeout: 10_000,
    navigationTimeout: 15_000,
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] },
    },
    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] },
    },
  ],

  webServer: {
    // APP_ENV=playwright tells Laravel to load .env.playwright (which pins
    // DB_DATABASE=sunroom_crm_test). Run database/seeders/PlaywrightSeeder.php
    // first via `php artisan migrate:fresh --seed --seeder=Database\\Seeders\\PlaywrightSeeder`
    // before invoking these tests.
    command: 'APP_ENV=playwright php artisan serve --host=127.0.0.1 --port=8000',
    url: 'http://127.0.0.1:8000/login',
    reuseExistingServer: !process.env.CI,
    timeout: 60_000,
    stdout: 'pipe',
    stderr: 'pipe',
  },
});
