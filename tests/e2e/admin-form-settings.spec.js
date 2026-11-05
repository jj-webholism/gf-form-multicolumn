const {test, expect} = require('@playwright/test');

test.describe('Form Settings Asset Enqueue Toggle Round-Trip', () => {

    const SETTINGS_URL = '/wp-admin/admin.php?page=gf_edit_forms&view=settings&subview=settings&id=4';
    const FRONTEND_FORM_URL = '/test-form/';

    test.beforeEach(async ({page}) => {
        // Authenticate into WP Admin
        await page.goto('/wp-login.php');
        await page.locator('#user_login').fill(process.env.WP_ADMIN_USER || 'admin_user');
        await page.locator('#user_pass').fill(process.env.WP_ADMIN_PASS || 'admin_pass');
        await page.locator('#wp-submit').click();
        await page.waitForURL('**/wp-admin/**');
    });

    test('disabling CSS checkbox suppresses stylesheet on frontend', async ({page}) => {
        // 1. Navigate to Form Settings
        await page.goto(SETTINGS_URL);
        const cssCheckbox = page.locator('input[type="checkbox"][name*="gfmcEnableCSS"]');
        const saveButton = page.locator('button[name="gform-settings-save"], input#gform-settings-save, button:has-text("Save Settings")');
        const alertNotice = page.getByRole('alert').or(page.getByText(/Settings updated/i));

        await cssCheckbox.waitFor({state: 'visible', timeout: 10000});

        // 2. Uncheck CSS setting if currently checked
        if (await cssCheckbox.isChecked()) {
            await cssCheckbox.uncheck();
            await saveButton.click();
            await expect(alertNotice).toBeVisible({timeout: 10000});
            await page.waitForLoadState('networkidle');
        }

        // 3. Visit Real Frontend Page and assert CSS is absent
        await page.goto(FRONTEND_FORM_URL);
        const cssLink = page.locator('link#gfmc_styles-css, link[href*="gf-form-multicolumn"]');
        await expect(cssLink).toHaveCount(0);

        // 4. Teardown: Re-enable CSS setting to restore clean baseline
        await page.goto(SETTINGS_URL);
        await cssCheckbox.waitFor({state: 'visible', timeout: 10000});
        await cssCheckbox.check();
        await saveButton.click();
        await expect(alertNotice).toBeVisible({timeout: 10000});
        await page.waitForLoadState('networkidle');

        // 5. Confirm CSS is restored on frontend
        await page.goto(FRONTEND_FORM_URL);
        await expect(page.locator('link#gfmc_styles-css, link[href*="gf-form-multicolumn"]')).toHaveCount(1);
    });

    test('enabling JS checkbox injects script on frontend', async ({page}) => {
        // 1. Navigate to Form Settings
        await page.goto(SETTINGS_URL);
        const jsCheckbox = page.locator('input[type="checkbox"][name*="gfmcEnableJS"]');
        const saveButton = page.locator('button[name="gform-settings-save"], input#gform-settings-save, button:has-text("Save Settings")');
        const alertNotice = page.getByRole('alert').or(page.getByText(/Settings updated/i));

        await jsCheckbox.waitFor({state: 'visible', timeout: 10000});

        // 2. Enable the JS helper checkbox
        await jsCheckbox.check();
        await saveButton.click();
        await expect(alertNotice).toBeVisible({timeout: 10000});
        await page.waitForLoadState('networkidle');

        // 3. Visit Real Frontend Page and verify <script> is present in the DOM
        await page.goto(FRONTEND_FORM_URL);
        const jsScript = page.locator('script#gfmc_scripts_public-js, script[src*="gf-form-multicolumn"]');
        await expect(jsScript).toHaveCount(1);

        // 4. Teardown: Restore JS checkbox to unchecked (default)
        await page.goto(SETTINGS_URL);
        await jsCheckbox.waitFor({state: 'visible', timeout: 10000});
        await jsCheckbox.uncheck();
        await saveButton.click();
        await expect(alertNotice).toBeVisible({timeout: 10000});
    });
});