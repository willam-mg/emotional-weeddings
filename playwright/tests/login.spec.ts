import { test, expect } from '@playwright/test';

test('admin login works', async ({ page }) => {
  const username = process.env.WP_ADMIN_USER;
  const password = process.env.WP_ADMIN_PASSWORD;

  test.skip(!username || !password, 'Set WP_ADMIN_USER and WP_ADMIN_PASSWORD to run this test.');

  await page.goto('/wp-login.php');
  await page.locator('#user_login').fill(username!);
  await page.locator('#user_pass').fill(password!);
  await page.locator('#wp-submit').click();

  await expect(page).toHaveURL(/wp-admin/);
  await expect(page.locator('#wpadminbar')).toBeVisible();
});
