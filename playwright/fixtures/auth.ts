import { test as base, expect, Page } from '@playwright/test';

/**
 * Credentials must match database/seeders/PlaywrightSeeder.php.
 */
export const SEEDED_ADMIN = {
  email: 'playwright-admin@sunroomcrm.test',
  password: 'playwright-password',
  name: 'Playwright Admin',
};

export const SEEDED_USER = {
  email: 'playwright-user@sunroomcrm.test',
  password: 'playwright-password',
  name: 'Playwright User',
};

/**
 * Submit the Volt login form for `email` and assert we landed on the dashboard.
 *
 * The form is wired to Livewire's `wire:model` so we type into the inputs and
 * submit via the form-level submit, which lets Livewire handle the round-trip.
 */
export async function loginVia(page: Page, email: string, password: string): Promise<void> {
  await page.goto('/login');
  await page.locator('#email').fill(email);
  await page.locator('#password').fill(password);
  await Promise.all([
    page.waitForURL(/\/dashboard$/),
    page.locator('button[type="submit"]').click(),
  ]);
  await expect(page.getByText('Total Contacts')).toBeVisible();
}

type AuthFixtures = {
  authedUserPage: Page;
  authedAdminPage: Page;
};

export const test = base.extend<AuthFixtures>({
  authedUserPage: async ({ page }, use) => {
    await loginVia(page, SEEDED_USER.email, SEEDED_USER.password);
    await use(page);
  },
  authedAdminPage: async ({ page }, use) => {
    await loginVia(page, SEEDED_ADMIN.email, SEEDED_ADMIN.password);
    await use(page);
  },
});

export { expect };
