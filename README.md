# Emotional Weddings WordPress

Local development workspace for the Emotional Weddings WordPress site.

## Environment

- WordPress root: `wordpress/`
- Local URL: `http://emotionalweddings.local:8080`
- Local admin URL: `http://emotionalweddings.local:8080/wp-admin/`
- Production URL: `https://www.emotionalweddings.rnova.tech`
- PHP: `8.3.31`
- MySQL: `8.0.30`
- Database: `rnovate2_wp49`
- Table prefix: `wpms_`
- Active theme: `Divi 4.27.6`
- Child theme: none detected

## Active Plugins

- `backuply-pro/backuply-pro.php`
- `backuply/backuply.php`
- `cookieadmin-pro/cookieadmin-pro.php`
- `cookieadmin/cookieadmin.php`
- `divi-pixel/divi-pixel.php`
- `gosmtp-pro/gosmtp-pro.php`
- `gosmtp/gosmtp.php`
- `loginizer-security/loginizer-security.php`
- `loginizer/loginizer.php`
- `mantenimiento-web/mantenimiento-web.php`
- `siteseo-pro/siteseo-pro.php`
- `siteseo/siteseo.php`
- `socialfeeds-pro/socialfeeds-pro.php`
- `socialfeeds/socialfeeds.php`
- `speedycache-pro/speedycache-pro.php`
- `speedycache/speedycache.php`

## Development Notes

Database backups are stored in `database/backups/` and ignored by Git. The initial local backup created during bootstrap is `database/backups/initial-2026-06-30.sql`.

Playwright tests are scaffolded under `playwright/tests/`. Copy `.env.example` to `.env` and set admin credentials before running login-dependent tests.

```powershell
npm install
npm run test:e2e
```
