import {test, expect} from '@playwright/test';

const ADMIN_USER = process.env.WP_ADMIN_USER || 'admin';
const ADMIN_PASS = process.env.WP_ADMIN_PASS || 'password';
const FORM_ID = process.env.WP_TEST_FORM_ID || '4';

test.describe('Gravity Forms Multi-Column Editor UI', () => {

    test.beforeEach(async ({page}) => {
        await page.goto('/wp-login.php');
        await page.locator('#user_login').fill(ADMIN_USER);
        await page.locator('#user_pass').fill(ADMIN_PASS);
        await page.locator('#wp-submit').click();
        await page.waitForURL('**/wp-admin/**');
    });

    test('verifies multi-column fields appear in the editor palette', async ({page}) => {
        await page.goto(`/wp-admin/admin.php?page=gf_edit_forms&id=${FORM_ID}`);
        const columnStartBtn = page.locator('button[data-type="column_start"]');
        await expect(columnStartBtn).toBeVisible();
    });
});