import {test, expect} from '@playwright/test';

test.describe('Automated Multi-Column Layout Test Suite', () => {

    test.beforeEach(async ({page}) => {
        // Authenticate as administrator
        await page.goto('/wp-login.php');
        await page.locator('#user_login').fill(process.env.WP_ADMIN_USER || 'admin_user');
        await page.locator('#user_pass').fill(process.env.WP_ADMIN_PASS || 'admin_pass');
        await page.locator('#wp-submit').click();
        await page.waitForURL('**/wp-admin/**');
    });

    test('discovers all GFMC forms and verifies preview column geometries', async ({page}) => {
        // 1. Visit Gravity Forms list screen
        await page.goto('/wp-admin/admin.php?page=gf_edit_forms');
        await page.waitForSelector('table.wp-list-table, table.gforms_form_list');

        // 2. Discover all test forms dynamically by title prefix
        const formEntries = await page.evaluate(() => {
            const forms = [];
            // Target form rows in GF list table
            const rows = document.querySelectorAll('table.wp-list-table tbody tr, table.gforms_form_list tbody tr');

            rows.forEach((row) => {
                const titleLink = row.querySelector('.row-title, a.title, .column-title a');
                if (!titleLink) {
                    return;
                }

                const title = titleLink.textContent?.trim() || '';
                const href = titleLink.getAttribute('href') || '';
                const idMatch = href.match(/[?&]id=(\d+)/);

                // Target all forms prefixed with "GFMC Test"
                if (idMatch && title.startsWith('GFMC Test')) {
                    forms.push({
                        id: idMatch[1],
                        title: title,
                    });
                }
            });

            return forms;
        });

        expect(formEntries.length).toBeGreaterThan(0);
        console.log(`Discovered ${formEntries.length} GFMC test forms:`, formEntries);

        // 3. Iterate through each discovered form and validate its layout in preview
        for (const {id, title} of formEntries) {
            console.log(`Testing Form #${id} - "${title}"`);

            await page.goto(`/?gf_page=preview&id=${id}`);
            await expect(page).toHaveURL(new RegExp(`gf_page=preview.*id=${id}`));

            // Skip edge-case resilience forms that intentionally don't form valid rows
            if (title.toLowerCase().includes('edge cases')) {
                // Ensure page rendered without unhandled PHP or layout crashes
                await expect(page.locator('.gform_wrapper')).toBeVisible();
                continue;
            }

            // Verify the multi-column stylesheet is loaded
            const stylesheet = page.locator('link#gf-form-multicolumn-css, link[href*="gf-form-multicolumn"]');
            await expect(stylesheet).toHaveCount(1);

            // Locate all column rows (handles both Modern Grid and Legacy markup)
            const rowLocator = page.locator('.gfmc-row, .gfmc-row-1-column, [class*="gfmc-row-"]');
            const rowCount = await rowLocator.count();

            // Validate geometric layout if column rows exist
            if (rowCount > 0) {
                // Find all column siblings within the form
                const columns = page.locator('.gfmc-col, .gfmc-column');
                const colCount = await columns.count();

                if (colCount >= 2) {
                    const boxes = [];
                    for (let i = 0; i < colCount; i++) {
                        const box = await columns.nth(i).boundingBox();
                        if (box) {
                            boxes.push(box);
                        }
                    }

                    // Test first row columns (e.g. columns sharing the same row level)
                    const rowOneCols = boxes.filter(b => Math.abs(b.y - boxes[0].y) < 25);

                    if (rowOneCols.length >= 2) {
                        // Assert tops align on the Y-axis
                        for (let i = 1; i < rowOneCols.length; i++) {
                            expect(Math.abs(rowOneCols[i].y - rowOneCols[0].y)).toBeLessThan(20);
                        }

                        // Assert left-to-right sequencing on the X-axis
                        for (let i = 1; i < rowOneCols.length; i++) {
                            expect(rowOneCols[i].x).toBeGreaterThanOrEqual(
                                rowOneCols[i - 1].x + (rowOneCols[i - 1].width * 0.85)
                            );
                        }
                    }
                }
            }
        }
    });

    test('tests dynamic form selection inside Gutenberg block canvas', async ({page}) => {
        // Navigate to the editor page containing a Gravity Forms block
        await page.goto('/wp-admin/post.php?post=11&action=edit');

        const iframeElement = page.locator('iframe[name="editor-canvas"]');
        const isIframe = await iframeElement.count() > 0;
        const canvas = isIframe ? page.frameLocator('iframe[name="editor-canvas"]') : page;

        // Ensure canvas has the stylesheet loaded
        const iframeCss = canvas.locator('link#gf-form-multicolumn-css, link[href*="gf-form-multicolumn"]');
        await expect(iframeCss).toHaveCount(1);

        // Verify the form present in the block canvas renders columns side-by-side
        const columns = canvas.locator('.gfmc-col, .gfmc-column');
        if (await columns.count() >= 2) {
            const box1 = await columns.nth(0).boundingBox();
            const box2 = await columns.nth(1).boundingBox();

            expect(box1).not.toBeNull();
            expect(box2).not.toBeNull();

            // Tops align within 20px
            expect(Math.abs(box1.y - box2.y)).toBeLessThan(20);
            // Column 2 placed horizontally after Column 1
            expect(box2.x).toBeGreaterThanOrEqual(box1.x + (box1.width * 0.85));
        }
    });
});