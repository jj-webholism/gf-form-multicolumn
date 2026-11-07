const {test, expect} = require('@playwright/test');

const ADMIN_USER = process.env.WP_ADMIN_USER || 'admin';
const ADMIN_PASS = process.env.WP_ADMIN_PASS || 'password';
const FORM_ID = process.env.WP_TEST_FORM_ID || '4';
const FRONTEND_FORM_URL = process.env.WP_FRONTEND_FORM_URL || `/?gf_page=preview&id=${FORM_ID}`;
const SETTINGS_URL = `/wp-admin/admin.php?page=gf_edit_forms&view=settings&subview=settings&id=${FORM_ID}`;

test.describe('Form Settings Layout & Stylesheet Dropdown Round-Trip', () => {

    test.beforeEach(async ({page}) => {
        await page.goto('/wp-login.php');
        await page.locator('#user_login').fill(ADMIN_USER);
        await page.locator('#user_pass').fill(ADMIN_PASS);
        await page.locator('#wp-submit').click();
        await page.waitForURL('**/wp-admin/**');
    });

    test('setting dropdown to grid_none renders div grid and suppresses stylesheet', async ({page}) => {
        await page.goto(SETTINGS_URL);
        const layoutSelect = page.locator('select[name*="gfmc_layout_css"]');
        const saveButton = page.locator('button[name="gform-settings-save"], button:has-text("Save Settings")');
        const alertNotice = page.getByRole('alert').or(page.getByText(/Settings updated/i));

        await layoutSelect.waitFor({state: 'visible', timeout: 10000});
        await layoutSelect.selectOption('grid_none');
        await saveButton.click();
        await expect(alertNotice).toBeVisible({timeout: 10000});

        await page.goto(FRONTEND_FORM_URL);
        await expect(page.locator('.gform_wrapper')).toBeVisible();
        await expect(page.locator('div.gfield')).not.toHaveCount(0);
        await expect(page.locator('li.gfield')).toHaveCount(0);
        await expect(page.locator('link#gf-form-multicolumn-css, link[href*="gf-form-multicolumn"]')).toHaveCount(0);

        // Teardown: restore default grid
        await page.goto(SETTINGS_URL);
        await layoutSelect.selectOption('grid');
        await saveButton.click();
        await expect(alertNotice).toBeVisible({timeout: 10000});
    });
});