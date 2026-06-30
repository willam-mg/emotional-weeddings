import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.WP_BASE_URL || 'https://www.emotionalweddings.rnova.tech';

export default defineConfig({
  testDir: './playwright/tests',
  outputDir: './playwright/screenshots/test-results',
  timeout: 30_000,
  expect: {
    timeout: 10_000
  },
  use: {
    baseURL,
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure'
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] }
    }
  ]
});

