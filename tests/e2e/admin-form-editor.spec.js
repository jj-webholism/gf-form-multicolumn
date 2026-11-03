import { test, expect } from '@playwright/test';

test.describe('Gravity Forms Multi-Column Admin UI', () => {

    test.beforeEach(async ({ page }) => {
        await page.goto('/wp-login.php');

        // Replace with your confirmed Local WP credentials
        await page.fill('#user_login', process.env.WP_ADMIN_USER || 'admin_user');
        await page.fill('#user_pass', process.env.WP_ADMIN_PASS || 'admin_pass');

        await page.click('#wp-submit');

        // If WordPress rejects the login, fail immediately with the error message
        const loginError = page.locator('#login_error');
        if (await loginError.isVisible()) {
            const errorText = await loginError.innerText();
            throw new Error(`WordPress Login Failed: ${errorText}`);
        }

        // Wait until successfully inside the dashboard
        await page.waitForURL(/.*\/wp-admin\/.*/);
    });

    test('verifies multi-column fields appear in the Gravity Forms editor palette', async ({ page }) => {
        // Navigate to your target test form
        await page.goto('/wp-admin/admin.php?page=gf_edit_forms&id=4');

        // Wait for the editor sidebar/palette to mount
        const columnStartBtn = page.locator('button[data-type="column_start"]');

        // If the custom fields are inside a collapsed accordion panel, reveal it:
        const layoutSection = page.locator('button:has-text("Layout Fields")');
        if (await layoutSection.isVisible()) {
            await layoutSection.click();
        }

        await expect(columnStartBtn).toBeVisible();
    });
});