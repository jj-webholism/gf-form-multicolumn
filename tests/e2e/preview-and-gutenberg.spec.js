import {test, expect} from '@playwright/test';

test.describe('Gravity Forms Multi-Column Preview & Gutenberg Layouts', () => {

    test.beforeEach(async ({page}) => {
        // Authenticate as WordPress administrator
        await page.goto('/wp-login.php');
        await page.locator('#user_login').fill(process.env.WP_ADMIN_USER || 'admin_user');
        await page.locator('#user_pass').fill(process.env.WP_ADMIN_PASS || 'admin_pass');
        await page.locator('#wp-submit').click();
        await page.waitForURL('**/wp-admin/**');
    });

    test('verifies multi-column layout renders side-by-side in GF standalone preview', async ({page}) => {
        // 1. Navigate to standalone preview for Form 6
        await page.goto('/?gf_page=preview&id=6');

        // Ensure we stayed on preview and were not bounced back to login
        await expect(page).toHaveURL(/.*gf_page=preview.*/);

        // 2. Verify multi-column stylesheet is present in the document head
        const previewCss = page.locator('link#gf-form-multicolumn-css, link[href*="gf-form-multicolumn"]');
        await expect(previewCss).toHaveCount(1);

        // 3. Locate the two column containers
        const col1 = page.locator('#gform_wrapper_6 .gfmc-row-1-col-1-of-2');
        const col2 = page.locator('#gform_wrapper_6 .gfmc-row-1-col-2-of-2');

        await expect(col1).toBeVisible();
        await expect(col2).toBeVisible();

        // 4. Measure geometry to verify horizontal multi-column alignment
        const box1 = await col1.boundingBox();
        const box2 = await col2.boundingBox();

        expect(box1).not.toBeNull();
        expect(box2).not.toBeNull();

        // Top edges align horizontally within 15px
        expect(Math.abs(box1.y - box2.y)).toBeLessThan(15);

        // Column 2 starts to the right of Column 1
        expect(box2.x).toBeGreaterThanOrEqual(box1.x + (box1.width * 0.9));
    });

    test('verifies multi-column layout renders side-by-side inside Gutenberg canvas iframe', async ({page}) => {
        // 1. Navigate to the post edit screen in Gutenberg
        await page.goto('/wp-admin/post.php?post=11&action=edit');
        await expect(page).toHaveURL(/.*post\.php\?post=11.*/);

        // 2. Locate Gutenberg canvas (handles both iframe and inline canvas modes)
        const iframeElement = page.locator('iframe[name="editor-canvas"]');
        const isIframe = await iframeElement.count() > 0;
        const canvas = isIframe ? page.frameLocator('iframe[name="editor-canvas"]') : page;

        // Wait for the form wrapper to be visible inside the editor
        const formWrapper = canvas.locator('#gform_wrapper_6');
        await expect(formWrapper).toBeVisible({timeout: 15000});

        // 3. Locate column containers
        const col1 = canvas.locator('#gform_wrapper_6 .gfmc-row-1-col-1-of-2');
        const col2 = canvas.locator('#gform_wrapper_6 .gfmc-row-1-col-2-of-2');

        await expect(col1).toBeVisible();
        await expect(col2).toBeVisible();

        // 4. Measure pixel positions
        const box1 = await col1.boundingBox();
        const box2 = await col2.boundingBox();

        expect(box1).not.toBeNull();
        expect(box2).not.toBeNull();

        // Tops align within 15px (side-by-side layout)
        expect(Math.abs(box1.y - box2.y)).toBeLessThan(15);

        // Column 2 is placed horizontally after Column 1
        expect(box2.x).toBeGreaterThanOrEqual(box1.x + (box1.width * 0.9));
    });
});