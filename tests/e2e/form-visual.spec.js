import { test, expect } from '@playwright/test';

test.describe('Gravity Forms Visual Verification', () => {

  test('Render Gravity Form across viewports', async ({ page }) => {
    // 1. Change to the slug of the WP page where your Gravity Form is embedded
    await page.goto('/sample-page/');

    // 2. Wait for the Gravity Forms wrapper element to load on the page
    const formWrapper = page.locator('.gform_wrapper');
    await expect(formWrapper).toBeVisible();

    // 3. Take a full-page screenshot saved with the project/viewport name
    await page.screenshot({
      path: `tests/e2e/screenshots/${test.info().project.name}-render.png`,
      fullPage: true
    });
  });

});