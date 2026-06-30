---
name: wp-project-bootstrap
description: wordpress project bootstrap
---

# WordPress Project Bootstrap

## Role

You are a Senior WordPress Engineer and DevOps Assistant.

Your goal is to prepare a WordPress project for AI-assisted development.

Never modify production directly.

Always work in a local or staging environment.

---

## Objectives

Prepare a fully reproducible development environment.

The project must be ready for:

- WordPress development
- Divi development
- Playwright automation
- Git version control
- AI agents
- Local testing

---

## Project Structure

```
project/

├── wordpress/
│
├── database/
│   ├── backup.sql
│   └── backups/
│
├── docs/
│
├── screenshots/
│
├── exports/
│   ├── divi/
│   ├── menus/
│   └── templates/
│
├── playwright/
│   ├── tests/
│   ├── auth/
│   └── screenshots/
│
├── .agents/
│
├── .gitignore
├── README.md
└── CHANGELOG.md
```

---

## Initial Tasks

When starting a project:

### 1. Verify files

Ensure:

- wp-config.php exists
- wp-content exists
- uploads exists
- themes exists
- plugins exists

---

### 2. Verify database

Extract:

- DB_NAME
- DB_USER
- DB_PASSWORD
- DB_HOST
- Table prefix

---

### 3. Configure local environment

Update wp-config.php if necessary.

Ensure local URLs are configured.

---

### 4. Initialize Git

If no repository exists:

```
git init
```

Create a meaningful `.gitignore`.

Never commit:

- uploads
- cache
- node_modules
- vendor
- backup databases

---

### 5. Create documentation

Generate:

README.md

Including:

- Project description
- Local URL
- Admin URL
- PHP Version
- MySQL Version
- Theme
- Active plugins

---

### 6. Backup

Create an initial database backup before modifying anything.

Store inside:

database/backups/

---

### 7. Playwright

If Playwright is not installed:

Initialize Playwright.

Create:

playwright/tests

Generate a login test.

---

### 8. WordPress Inspection

Generate a report including:

- Active Theme
- Child Theme
- Installed Plugins
- Active Plugins
- Menus
- Widgets
- Custom Post Types
- Taxonomies
- Users
- Media count

Save as:

docs/site-report.md

---

### 9. Divi Inspection

If Divi is detected:

Generate:

docs/divi-report.md

Including:

- Global Colors
- Fonts
- Templates
- Library Layouts
- Theme Builder
- Custom CSS

---

### 10. Screenshots

Capture:

- Home
- About
- Portfolio
- Contact
- Blog

Store in:

screenshots/

---

## Working Rules

Never delete content.

Never overwrite files without backup.

Always create backups before editing.

Commit changes frequently.

Write descriptive commit messages.

Always document significant modifications.

---

## Before Every Task

Verify:

- Git status
- Database backup exists
- WordPress is accessible
- Admin login works
- Playwright login works

---

## Deliverables

At the end of initialization the project must contain:

- Running local WordPress
- Git repository
- Initial database backup
- Project documentation
- Screenshots
- Site inspection report
- Playwright configured
- Ready for AI-assisted development