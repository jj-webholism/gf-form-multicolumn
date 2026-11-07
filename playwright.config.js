// @ts-check
const {defineConfig, devices} = require('@playwright/test');
const fs = require('fs');
const path = require('path');

// Load environment variables from .env if present
const envPath = path.resolve(__dirname, '.env');
if (fs.existsSync(envPath)) {
    const envFile = fs.readFileSync(envPath, 'utf8');
    envFile.split('\n').forEach((line) => {
        const trimmed = line.trim();
        if (!trimmed || trimmed.startsWith('#')) return;
        const [key, ...rest] = trimmed.split('=');
        if (key && rest.length > 0) {
            const val = rest.join('=').trim().replace(/^['"]|['"]$/g, '');
            if (!process.env[key.trim()]) {
                process.env[key.trim()] = val;
            }
        }
    });
}

module.exports = defineConfig({
    testDir: './tests/e2e',
    timeout: 30 * 1000,
    expect: {
        timeout: 5000,
    },
    fullyParallel: false,
    reporter: 'html',
    use: {
        ignoreHTTPSErrors: true,
        // Fall back to neutral localhost if not supplied via .env
        baseURL: process.env.WP_BASE_URL || 'http://localhost:8888',
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