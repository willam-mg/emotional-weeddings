import { test, expect } from '@playwright/test';

const pages = [
  { name: 'home', path: '/' },
  { name: 'about', path: '/about/' },
  { name: 'services', path: '/services/' },
  { name: 'gallery', path: '/gallery/' },
  { name: 'journal', path: '/journal/' },
  { name: 'faq', path: '/faq/' },
  { name: 'contact', path: '/contact/' }
];

test('capture authenticated audit screenshots', async ({ page }) => {
  test.setTimeout(90_000);

  const username = process.env.WP_ADMIN_USER;
  const password = process.env.WP_ADMIN_PASSWORD;

  test.skip(!username || !password, 'Set WP_ADMIN_USER and WP_ADMIN_PASSWORD to capture audit screenshots.');

  await page.goto('/wp-login.php');
  await page.locator('#user_login').fill(username!);
  await page.locator('#user_pass').fill(password!);
  await page.locator('#wp-submit').click();
  await expect(page.locator('#wpadminbar')).toBeVisible();

  for (const pageInfo of pages) {
    await page.goto(pageInfo.path);
    await page.getByRole('button', { name: /Aceptar Todo|Accept All/i }).click({ timeout: 3_000 }).catch(() => {});
    await expect(page.locator('body')).toBeVisible();
    await page.screenshot({
      path: `screenshots/audit-${pageInfo.name}.png`,
      fullPage: true
    });
  }
});
