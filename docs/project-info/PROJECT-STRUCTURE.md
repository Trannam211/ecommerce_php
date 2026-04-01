# Project Structure

This document describes the reorganized structure used to keep the project cleaner and easier to maintain.

## Key Directories

- `admin/`: Admin panel pages and admin-specific static files.
- `app/frontend/shared/`: Shared frontend partials used across many public pages.
- `assets/`: Frontend static assets (css/js/fonts/images/uploads).
- `database/`: SQL schema and seed dump.
- `docs/project-info/`: Environment notes and login details.
- `docs/screenshots/`: Screenshots for documentation and demos.
- `payment/`: Payment integration modules (bank/paypal).
- `scripts/`: Utility scripts for maintenance/setup.
- `tests/manual/`: Manual test files.

## Compatibility Layer

To avoid breaking legacy include paths, these root files are retained as bridge loaders:

- `header.php`
- `footer.php`
- `customer-sidebar.php`
- `sidebar-category.php`

Each bridge file forwards to its real implementation under `app/frontend/shared/`.

## Notes

- Public URLs remain unchanged.
- Existing `require_once('header.php')` and similar includes continue to work.
- SQL import path is now `database/ecommerceweb.sql`.

## Setup notes

- XAMPP MySQL/MariaDB utf8mb4 (tiếng Việt): `docs/project-info/xampp-mysql-utf8mb4.md`.