import { test, expect } from '@playwright/test';

test('admin login works', async ({ page }) => {
  const username = process.env.WP_ADMIN_USER;
  const password = process.env.WP_ADMIN_PASSWORD;

  test.skip(!username || !password, 'Set WP_ADMIN_USER and WP_ADMIN_PASSWORD to run this test.');

  await page.goto('/wp-login.php');
  await page.getByLabel('Username or Email Address').fill(username!);
  await page.getByLabel('Password').fill(password!);
  await page.getByRole('button', { name: 'Log In' }).click();

  await expect(page).toHaveURL(/wp-admin/);
  await expect(page.locator('#wpadminbar')).toBeVisible();
});

