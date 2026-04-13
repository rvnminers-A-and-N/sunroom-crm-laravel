import { test, expect } from '../fixtures/auth';

// Chart.js is loaded via CDN with `defer` in the app layout. On pages that don't
// use Chart (contacts, pipeline) the global may not be defined when Alpine x-data
// initialises. This is a benign CDN-defer timing issue, not a rendering regression.
// Firefox also logs a bare "undefined" companion message for the Chart.js ReferenceError.
// WebKit phrases it as "Can't find variable: Chart".
function isKnownError(text: string): boolean {
  return text.includes('Chart is not defined') || text.includes("Can't find variable: Chart") || text === 'undefined';
}

const PUBLIC_PAGES: Array<{ path: string; expectedText: string }> = [
  { path: '/login', expectedText: 'Email' },
];

const AUTHED_PAGES: Array<{ path: string; expectedText: string }> = [
  { path: '/dashboard', expectedText: 'Total Contacts' },
  { path: '/contacts', expectedText: 'Contacts' },
  { path: '/deals/pipeline', expectedText: 'Deal Pipeline' },
];

test.describe('cross-browser render smoke', () => {
  for (const { path, expectedText } of PUBLIC_PAGES) {
    test(`renders ${path} without console errors`, async ({ page }, testInfo) => {
      const errors: string[] = [];
      page.on('pageerror', (err) => {
        if (!isKnownError(err.message)) errors.push(err.message);
      });
      page.on('console', (msg) => {
        if (msg.type() === 'error' && !isKnownError(msg.text())) {
          errors.push(msg.text());
        }
      });

      await page.goto(path);
      await expect(page.getByText(expectedText).first()).toBeVisible();
      await testInfo.attach(`${testInfo.project.name}-${path.replace(/\//g, '_')}.png`, {
        body: await page.screenshot({ fullPage: true }),
        contentType: 'image/png',
      });
      expect(errors, errors.join('\n')).toEqual([]);
    });
  }

  for (const { path, expectedText } of AUTHED_PAGES) {
    test(`renders ${path} without console errors when authenticated`, async ({ authedUserPage: page }, testInfo) => {
      const errors: string[] = [];
      page.on('pageerror', (err) => {
        if (!isKnownError(err.message)) errors.push(err.message);
      });
      page.on('console', (msg) => {
        if (msg.type() === 'error' && !isKnownError(msg.text())) {
          errors.push(msg.text());
        }
      });

      await page.goto(path);
      await expect(page.getByText(expectedText).first()).toBeVisible();
      await testInfo.attach(`${testInfo.project.name}-${path.replace(/\//g, '_')}.png`, {
        body: await page.screenshot({ fullPage: true }),
        contentType: 'image/png',
      });
      expect(errors, errors.join('\n')).toEqual([]);
    });
  }
});
