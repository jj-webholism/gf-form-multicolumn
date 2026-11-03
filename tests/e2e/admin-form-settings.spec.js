test('verifies CSS and JS checkboxes appear in Form Settings', async ({ page }) => {
    // 1. Navigate to Form Settings for Form ID 4
    await page.goto('/wp-admin/admin.php?page=gf_edit_forms&view=settings&id=4');

    // 2. Locate the checkboxes by ID or label
    // (Adjust the selectors to match your plugin's field names, e.g. gfmcEnableCSS, gfmc_enable_css)
    const cssCheckbox = page.locator('input[type="checkbox"][name*="css" i], input#gfmcEnableCSS');
    const jsCheckbox  = page.locator('input[type="checkbox"][name*="js" i], input#gfmcEnableJS');

    // 3. Assert visibility
    await expect(cssCheckbox).toBeVisible({ timeout: 5000 });
    await expect(jsCheckbox).toBeVisible({ timeout: 5000 });
});