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

Requirements: PHP 8.2 or newer, Composer 2, and PHP's SQLite and `pdo_pgsql` extensions (the latter is required for Neon). From the project root in PowerShell:

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

SQLite is used locally while `DATABASE_URL` is empty. To switch to Neon, open the Neon Console, choose your project and branch, select **Connect**, and copy the pooled PostgreSQL connection string. Paste it as the value of `DATABASE_URL` in `.env`—no other database setting needs to change. The app selects PostgreSQL automatically when this value is set. Keep the URL private; it includes your database password. The provided project-local PHP runtime includes the `pdo_pgsql` extension.

Neon uses transaction pooling on pooled endpoints. Use the **direct (non-pooled)** endpoint for schema migrations. The `.\run-civicdesk.ps1` startup script detects a Neon `-pooler` endpoint and temporarily uses its corresponding direct hostname for `php artisan migrate`, then restores the pooled URL for the application. To seed a fresh Neon database, first run `php artisan migrate --seed --force` with `DATABASE_URL` set to the **direct** connection string, or use the direct URL temporarily in `.env`; then use the pooled URL for normal app requests. This creates a fresh hosted database; it does not copy data from local SQLite.

Set `ADMIN_EMAIL` and `ADMIN_PASSWORD` in the same `.env` before seeding. Use a unique password of at least 12 characters. Changing these environment values does not change an administrator who is already in the database; run `php artisan db:seed --class=AdminUserSeeder` after changing them.

Neon stores the application's database records, not image files. Image uploads currently use Laravel's local public filesystem disk and therefore need a separate persistent object-storage service before deploying to Vercel. Never point the Vercel deployment at a local SQLite database or filesystem for persistent application data.

To use MySQL instead, set `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env` before migrating.

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

### Deploying to Vercel

The repository includes a Vercel PHP Function entry point and route configuration. In Vercel, set the **Root Directory** to the project root (the folder containing `vercel.json` and `composer.json`), then redeploy. Do not set the root directory to `public`; Vercel needs the Laravel application and its Composer dependencies to build the PHP function.

Add these environment variables in the Vercel project settings for each environment you deploy:

- `APP_KEY`: the existing Laravel application key. Keep it private and consistent between deployments.
- `APP_ENV`: `production`
- `APP_DEBUG`: `false`
- `APP_URL`: the deployed HTTPS domain
- `DATABASE_URL`: the Neon PostgreSQL connection string. The application reads `DATABASE_URL` for PostgreSQL; setting only `DB_URL` will not select Neon.
- `ADMIN_EMAIL` and `ADMIN_PASSWORD`: the administrator credentials used when seeding the database.
- `SESSION_DRIVER`: `cookie` to avoid relying on Vercel's temporary filesystem for login sessions.
- `CACHE_STORE`: `array`
- `LOG_CHANNEL`: `stderr`
- `VIEW_COMPILED_PATH`: `/tmp/civicdesk-views`

Run pending migrations and seed the administrator against Neon using its **direct** (non-pooled) connection before deploying or signing in. Use the pooled URL as `DATABASE_URL` for normal Vercel requests. Vercel's filesystem is temporary, so uploaded images must be moved to durable object storage; local image uploads will not persist across function invocations.
