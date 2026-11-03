import { test, expect } from '@playwright/test';

test.describe('Gravity Forms Multi-Column Editor UI', () => {

  test.beforeEach(async ({ page }) => {
    // 1. Visit the login page
    await page.goto('http://localhost:8888/wp-login.php');

    // 2. Fill in your Local WP admin username and password
    await page.fill('#user_login', process.env.WP_ADMIN_USER || 'admin_user');
    await page.fill('#user_pass', process.env.WP_ADMIN_PASS || 'admin_pass');

    // 3. Submit and wait for redirect to wp-admin
    await Promise.all([
      page.waitForURL(/.*wp-admin.*/),
      page.click('#wp-submit'),
    ]);
  });

  test('verifies multi-column fields appear in the editor palette', async ({ page }) => {
    // 4. Navigate directly to the form editor (adjust ID to match your test form)
    await page.goto('http://localhost:8888/wp-admin/admin.php?page=gf_edit_forms&id=4');

    // 5. Open the field palette if collapsed, or locate the field button
    const columnStartBtn = page.locator('button[data-type="column_start"]');
    await expect(columnStartBtn).toBeVisible();
  });
});