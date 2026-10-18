# CivicDesk — Public Service Request System

CivicDesk is a Laravel 12 application where citizens can report local service issues and track their progress. Administrators can review reports, update case statuses, and see service-request analytics.

## Open the running app

The app is set up on the current Windows account and is served locally at <http://127.0.0.1:8000>.

An administrator account has been created using the `ADMIN_EMAIL` and `ADMIN_PASSWORD` values in the local `.env` file. To retrieve the configured login in PowerShell without sharing or saving the password in this README:

```powershell
Get-Content .env | Where-Object { $_ -match '^ADMIN_EMAIL=|^ADMIN_PASSWORD=' }
```

Keep the password private. Citizens can register an account from the sign-in screen.

## Start the app again

From the project folder, run:

```powershell
.\run-civicdesk.ps1
```

The script uses the project-local PHP runtime, applies pending database migrations, and starts Laravel at <http://127.0.0.1:8000>. Leave that PowerShell window open while using the app. Press **Ctrl+C** in that window to stop the server.

PHP 8.3 and Composer 2 are installed in the current Windows user's local application-data folder. Open a **new** PowerShell window after setup to use the `composer` command. The existing XAMPP PHP on PATH may still be older; the CivicDesk start script selects its own PHP 8.3 runtime.

## Fresh installation on another machine

Requirements: PHP 8.2 or newer, Composer 2, and PHP's SQLite extension. From the project root in PowerShell:

```powershell
composer install
Copy-Item .env.example .env
```

Set a private `ADMIN_PASSWORD` of at least 12 characters in `.env`, then initialize the application:

```powershell
php artisan key:generate
New-Item -ItemType File -Force database/database.sqlite
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Use the `ADMIN_EMAIL` and `ADMIN_PASSWORD` configured in `.env` to sign in to the administrator portal. The seed command creates an administrator and sample dashboard requests. To create only the administrator, replace `php artisan migrate --seed` with:

```powershell
php artisan migrate
php artisan db:seed --class=AdminUserSeeder
```

If `php -v` reports an older version, configure PHP 8.2+ in your PATH or use its full executable path for Composer and Artisan commands. On the original setup machine, `.\run-civicdesk.ps1` handles the PHP version automatically.

SQLite is configured by default. To use MySQL, set `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env` before migrating.

## Features

- Citizen registration, login, and logout using Laravel's server-side sessions.
- Requests with category, location, description, and optional JPEG, PNG, or WebP image (up to 1.5 MB).
- Citizen request history with search, category/status filters, and status tracking.
- Admin-only request management and dashboard analytics.
- Status updates constrained to Pending → In Progress → Resolved.
- Responsive desktop and mobile layouts.

## Tests

Run the feature tests with:

```powershell
php artisan test
```

The suite covers registration, authorization, request creation, image storage, citizen request visibility, and status transitions.

## Production deployment

Point the web server document root to `public/`, not the repository root. Configure production environment values, HTTPS, `APP_DEBUG=false`, a strong `APP_KEY`, and durable database and file storage. This starter stores uploaded photos on Laravel's public disk; use private storage and authenticated image delivery if photos may contain sensitive information. Never commit `.env` or disclose administrator credentials.
