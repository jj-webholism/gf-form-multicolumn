const {test, expect} = require('@playwright/test');

test.describe('Form Settings Layout & Stylesheet Dropdown Round-Trip', () => {

    const SETTINGS_URL = '/wp-admin/admin.php?page=gf_edit_forms&view=settings&subview=settings&id=4';
    const FRONTEND_FORM_URL = '/test-form/';

    test.beforeEach(async ({page}) => {
        await page.goto('/wp-login.php');
        await page.locator('#user_login').fill(process.env.WP_ADMIN_USER || 'admin_user');
        await page.locator('#user_pass').fill(process.env.WP_ADMIN_PASS || 'admin_pass');
        await page.locator('#wp-submit').click();
        await page.waitForURL('**/wp-admin/**');
    });

    test('setting dropdown to grid_none renders div grid and suppresses stylesheet', async ({page}) => {
        // 1. Navigate to Settings and select 'grid_none'
        await page.goto(SETTINGS_URL);
        const layoutSelect = page.locator('select[name*="gfmc_layout_css"]');
        const saveButton = page.locator('button[name="gform-settings-save"], button:has-text("Save Settings")');
        const alertNotice = page.getByRole('alert').or(page.getByText(/Settings updated/i));

        await layoutSelect.waitFor({state: 'visible', timeout: 10000});
        await layoutSelect.selectOption('grid_none');
        await saveButton.click();
        await expect(alertNotice).toBeVisible({timeout: 10000});

        // 2. Frontend check: Form renders, div structure present, ul/li absent, stylesheet absent
        await page.goto(FRONTEND_FORM_URL);
        await expect(page.locator('.gform_wrapper')).toBeVisible();
        await expect(page.locator('div.gfield')).not.toHaveCount(0);
        await expect(page.locator('li.gfield')).toHaveCount(0);
        await expect(page.locator('link#gf-form-multicolumn-css, link[href*="gf-form-multicolumn"]')).toHaveCount(0);

        // 3. Teardown: Restore to default 'grid'
        await page.goto(SETTINGS_URL);
        await layoutSelect.waitFor({state: 'visible', timeout: 10000});
        await layoutSelect.selectOption('grid');
        await saveButton.click();
        await expect(alertNotice).toBeVisible({timeout: 10000});
    });

    test('setting dropdown to legacy renders ul/li list and loads stylesheet', async ({page}) => {
        // 1. Navigate to Settings and select 'legacy'
        await page.goto(SETTINGS_URL);
        const layoutSelect = page.locator('select[name*="gfmc_layout_css"]');
        const saveButton = page.locator('button[name="gform-settings-save"], button:has-text("Save Settings")');
        const alertNotice = page.getByRole('alert').or(page.getByText(/Settings updated/i));

        await layoutSelect.waitFor({state: 'visible', timeout: 10000});
        await layoutSelect.selectOption('legacy');
        await saveButton.click();
        await expect(alertNotice).toBeVisible({timeout: 10000});

        // 2. Frontend check: Form renders, li list structure present, stylesheet loaded
        await page.goto(FRONTEND_FORM_URL);
        await expect(page.locator('.gform_wrapper')).toBeVisible();
        await expect(page.locator('li.gfield')).not.toHaveCount(0);
        await expect(page.locator('link#gf-form-multicolumn-css, link[href*="gf-form-multicolumn"]')).toHaveCount(1);

        // 3. Teardown: Restore to default 'grid'
        await page.goto(SETTINGS_URL);
        await layoutSelect.waitFor({state: 'visible', timeout: 10000});
        await layoutSelect.selectOption('grid');
        await saveButton.click();
        await expect(alertNotice).toBeVisible({timeout: 10000});
    });
});