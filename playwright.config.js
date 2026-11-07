// @ts-check
const {defineConfig, devices} = require('@playwright/test');

module.exports = defineConfig({
    testDir: './tests/e2e',
    timeout: 30 * 1000,
    expect: {
        timeout: 5000,
    },
    fullyParallel: false, // Run sequentially for reliable WordPress session state
    reporter: 'html',
    use: {
        // Automatically accept Local WP self-signed SSL certificates
        ignoreHTTPSErrors: true,
        // Base URL using HTTPS
        baseURL: 'http://localhost:8888',
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
    },
    projects: [
        {
            name: 'chromium',
            use: {...devices['Desktop Chrome']},
        },
    ],
});