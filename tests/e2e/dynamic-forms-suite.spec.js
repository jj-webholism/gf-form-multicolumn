import {test, expect} from '@playwright/test';

const ADMIN_USER = process.env.WP_ADMIN_USER || 'admin';
const ADMIN_PASS = process.env.WP_ADMIN_PASS || 'password';
const POST_ID = process.env.WP_TEST_POST_ID || '11';

test.describe('Automated Multi-Column Layout Test Suite', () => {

    test.beforeEach(async ({page}) => {
        await page.goto('/wp-login.php');
        await page.locator('#user_login').fill(ADMIN_USER);
        await page.locator('#user_pass').fill(ADMIN_PASS);
        await page.locator('#wp-submit').click();
        await page.waitForURL('**/wp-admin/**');
    });

    test('discovers all GFMC forms and verifies preview column geometries', async ({page}) => {
        await page.goto('/wp-admin/admin.php?page=gf_edit_forms');
        await page.waitForSelector('table.wp-list-table, table.gforms_form_list');

        const formEntries = await page.evaluate(() => {
            const forms = [];
            const rows = document.querySelectorAll('table.wp-list-table tbody tr, table.gforms_form_list tbody tr');

            rows.forEach((row) => {
                const titleLink = row.querySelector('.row-title, a.title, .column-title a');
                if (!titleLink) return;

                const title = titleLink.textContent?.trim() || '';
                const href = titleLink.getAttribute('href') || '';
                const idMatch = href.match(/[?&]id=(\d+)/);

                if (idMatch && title.startsWith('GFMC Test')) {
                    forms.push({id: idMatch[1], title});
                }
            });

            return forms;
        });

        expect(formEntries.length).toBeGreaterThan(0);

        for (const {id, title} of formEntries) {
            await page.goto(`/?gf_page=preview&id=${id}`);
            await expect(page).toHaveURL(new RegExp(`gf_page=preview.*id=${id}`));

            if (title.toLowerCase().includes('edge cases')) {
                await expect(page.locator('.gform_wrapper')).toBeVisible();
                continue;
            }

            const stylesheet = page.locator('link#gf-form-multicolumn-css, link[href*="gf-form-multicolumn"]');
            await expect(stylesheet).toHaveCount(1);

            const rowLocator = page.locator('.gfmc-row, .gfmc-row-1-column, [class*="gfmc-row-"]');
            if (await rowLocator.count() > 0) {
                const columns = page.locator('.gfmc-col, .gfmc-column');
                const colCount = await columns.count();

                if (colCount >= 2) {
                    const boxes = [];
                    for (let i = 0; i < colCount; i++) {
                        const box = await columns.nth(i).boundingBox();
                        if (box) boxes.push(box);
                    }

                    const rowOneCols = boxes.filter(b => Math.abs(b.y - boxes[0].y) < 25);
                    if (rowOneCols.length >= 2) {
                        for (let i = 1; i < rowOneCols.length; i++) {
                            expect(Math.abs(rowOneCols[i].y - rowOneCols[0].y)).toBeLessThan(20);
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
        await page.goto(`/wp-admin/post.php?post=${POST_ID}&action=edit`);

        const iframeElement = page.locator('iframe[name="editor-canvas"]');
        const isIframe = await iframeElement.count() > 0;
        const canvas = isIframe ? page.frameLocator('iframe[name="editor-canvas"]') : page;

        const iframeCss = canvas.locator('link#gf-form-multicolumn-css, link[href*="gf-form-multicolumn"]');
        await expect(iframeCss).toHaveCount(1);

        const columns = canvas.locator('.gfmc-col, .gfmc-column');
        if (await columns.count() >= 2) {
            const box1 = await columns.nth(0).boundingBox();
            const box2 = await columns.nth(1).boundingBox();

            expect(box1).not.toBeNull();
            expect(box2).not.toBeNull();
            expect(Math.abs(box1.y - box2.y)).toBeLessThan(20);
            expect(box2.x).toBeGreaterThanOrEqual(box1.x + (box1.width * 0.85));
        }
    });
});