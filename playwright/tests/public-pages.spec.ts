import { test, expect } from '@playwright/test';

const pages = [
  { name: 'home', path: '/' },
  { name: 'about', path: '/about/' },
  { name: 'gallery', path: '/gallery/' },
  { name: 'contact', path: '/contact/' },
  { name: 'journal', path: '/journal/' }
];

for (const pageInfo of pages) {
  test(`${pageInfo.name} page responds`, async ({ page }) => {
    const response = await page.goto(pageInfo.path);
    expect(response?.ok()).toBeTruthy();
    await page.screenshot({ path: `playwright/screenshots/${pageInfo.name}.png`, fullPage: true });
  });
}
