import { defineConfig, devices } from '@playwright/test';
import { existsSync, readFileSync } from 'node:fs';

function loadLocalEnv(path = '.env') {
  if (!existsSync(path)) return;

  for (const line of readFileSync(path, 'utf8').split(/\r?\n/)) {
    const trimmed = line.trim();
    if (!trimmed || trimmed.startsWith('#')) continue;

    const separator = trimmed.indexOf('=');
    if (separator === -1) continue;

    const key = trimmed.slice(0, separator).trim();
    const value = trimmed.slice(separator + 1).trim();
    process.env[key] ??= value;
  }
}

loadLocalEnv();

const baseURL = process.env.WP_BASE_URL || 'http://emotionalweddings.local:8080';

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
