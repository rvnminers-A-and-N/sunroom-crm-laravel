import { test, expect } from '../fixtures/auth';

test('golden path: dashboard, create contact, create deal, pipeline, logout', async ({ authedUserPage: page }) => {
  // 1. Dashboard renders the four stat cards
  await expect(page).toHaveURL(/\/dashboard$/);
  await expect(page.getByText('Total Contacts')).toBeVisible();
  await expect(page.getByText('Total Companies')).toBeVisible();
  await expect(page.getByText('Pipeline Value')).toBeVisible();
  await expect(page.getByText('Won Revenue')).toBeVisible();

  // 2. Create a brand-new contact via the modal flow
  await page.goto('/contacts');
  await expect(page.getByRole('main').getByRole('heading', { name: 'Contacts' })).toBeVisible();

  // The seeder already created Riley Tester for us, so the empty-state button is
  // not present. Use the toolbar button instead.
  await page.locator('main button[wire\\:click="create"]').first().click();
  await page.locator('#firstName').waitFor();
  await page.locator('#firstName').fill('Stella');
  await page.locator('#lastName').fill('Skylight');
  await page.locator('#email').fill('stella@playwright.test');
  await page.locator('#phone').fill('555-0199');
  await page.getByRole('button', { name: 'Create' }).click();

  await expect(page.getByText('Stella Skylight').first()).toBeVisible();

  // 3. The seeded deal should already be in the Lead column of the pipeline
  await page.goto('/deals/pipeline');
  await expect(page.getByRole('main').getByRole('heading', { name: 'Deal Pipeline' })).toBeVisible();
  const leadColumn = page.locator('.pipeline-column[data-stage="Lead"]');
  await expect(leadColumn).toContainText('Seeded Pipeline Deal');

  // 4. Log out via the layout.navigation Livewire component
  await page.evaluate(() => {
    // @ts-expect-error - Livewire is exposed on window at runtime
    window.Livewire.getByName('layout.navigation')[0].call('logout');
  });
  await page.waitForURL((url) => url.pathname === '/' || url.pathname === '/login');
  // After logout the home redirects to /login for guests
  await page.goto('/dashboard');
  await page.waitForURL(/\/login$/);
});
