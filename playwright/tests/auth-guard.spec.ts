import { test, expect, loginVia, SEEDED_USER } from '../fixtures/auth';

test.describe('auth guards', () => {
  test('redirects unauthenticated users from /dashboard to /login', async ({ page }) => {
    const response = await page.goto('/dashboard');
    expect(response).not.toBeNull();
    await page.waitForURL(/\/login$/);
    await expect(page.locator('#email')).toBeVisible();
  });

  test('returns a 403 page when a non-admin visits /admin/users', async ({ page }) => {
    await loginVia(page, SEEDED_USER.email, SEEDED_USER.password);
    const response = await page.goto('/admin/users');
    expect(response?.status()).toBe(403);
    await expect(page.getByText('403', { exact: false })).toBeVisible();
  });
});
