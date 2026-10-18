# CivicDesk

CivicDesk is a public service request portal. Residents can report local issues, track progress, and see status updates. Staff can review incoming requests and move cases through **Pending → In Progress → Resolved**.

## What it includes

- Resident registration, sign in, and sign out.
- Request submission with category, location, description, and an optional image.
- Private request lists: residents can see only their own cases.
- Staff dashboard with case counts, open/resolved totals, and registered resident count.
- Search and filters for staff and residents.
- Staff-only, one-step status changes with a dated public update history.
- Private image access: only the resident who submitted the case and staff can view its image.
- Responsive desktop and mobile layouts.

## Stack and structure

- Laravel 12 and PHP 8.2 or later.
- PostgreSQL in production; SQLite for local development.
- Blade templates and a small, build-free CSS file.
- Vercel PHP Function adapter for the current Vercel deployment.

```text
app/Http/Controllers/       Request handling
app/Http/Requests/          Input validation and authorization
app/Models/                 Residents, requests, and status history
app/Policies/               Request visibility rules
database/migrations/        Database schema
database/seeders/           Staff account and local demo data
resources/views/            Blade pages
public/css/                 Stylesheet (no frontend build step)
api/index.php               Vercel entry point
vercel.json                 Vercel PHP routing
```

## Run locally

Requirements: PHP 8.2+, Composer 2, and the PHP SQLite extension.

1. Install dependencies and create the local environment file:

   ```powershell
   composer install
   Copy-Item .env.example .env
   php artisan key:generate
   ```

2. In `.env`, set `ADMIN_EMAIL` and a unique `ADMIN_PASSWORD` of at least 12 characters. Leave `DATABASE_URL` empty to use SQLite.
3. Create the local database and seed it:

   ```powershell
   New-Item -ItemType File -Force database/database.sqlite
   php artisan migrate --seed
   ```

4. Start Laravel:

   ```powershell
   php artisan serve
   ```

Open <http://127.0.0.1:8000>. Local seeding creates the configured staff account and sample requests. Production seeding creates only the staff account, never demo cases.

On the original Windows development machine, `./run-civicdesk.ps1` selects the project’s PHP 8.3 runtime, migrates the local database, and starts the server.

## Deploy to Vercel

The repository includes a PHP Function entry point and Vercel routing. `package.json` pins the Node runtime to **22.x**, which the `vercel-php@0.9.0` adapter expects. Keep the Vercel project’s **Root Directory** at `./` (the directory containing `composer.json` and `vercel.json`) and use the **Other** preset. No frontend build or output directory is required.

The Vercel PHP runtime installs Composer dependencies for the function. `laravel/pail` remains a development-only dependency; Composer's production install omits it, then the `vercel` Composer script rebuilds Laravel's package manifest from the installed production packages. Generated `bootstrap/cache/*.php` manifests are intentionally excluded from Git so a local development manifest cannot register missing development providers in production.

### 1. Configure production environment variables

Add these individually in **Vercel → Project → Settings → Environment Variables**, selecting **Production** for each. Do not commit `.env` or paste it into the repository.

| Name | Value |
| --- | --- |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | A generated Laravel key, kept private and stable across deployments |
| `APP_URL` | The deployed `https://` URL |
| `DATABASE_URL` | Neon pooled PostgreSQL connection URL |
| `SESSION_DRIVER` | `cookie` |
| `SESSION_ENCRYPT` | `true` |
| `CACHE_STORE` | `array` |
| `LOG_CHANNEL` | `stderr` |
| `VIEW_COMPILED_PATH` | `/tmp/civicdesk-views` |
| `FILESYSTEM_DISK` | `s3` when persistent image storage is configured |
| `ADMIN_EMAIL` | Staff account email, for the one-time seeder |
| `ADMIN_PASSWORD` | Unique password of at least 12 characters, for the one-time seeder |

Generate a key locally with `php artisan key:generate --show` if you do not already have one. Copy its output directly into Vercel’s `APP_KEY` value. Never paste it into chat, a README, or source code. Redeploy after changing environment variables; existing deployments do not receive new values.

### 2. Configure persistent database and images

Use Neon’s **pooled** URL for `DATABASE_URL` during web requests. Use its **direct** URL only when running schema migrations. From a trusted machine, temporarily set `DATABASE_URL` to Neon’s direct URL (and keep it out of Git), then run `php artisan migrate --force` against production. To create the staff account once, set `ADMIN_EMAIL` and `ADMIN_PASSWORD` in the same secure environment and run:

```powershell
php artisan db:seed --class=AdminUserSeeder --force
```

Use the specific `AdminUserSeeder` command above in production. `DatabaseSeeder` also creates demonstration cases in local development. After migrations and seeding, restore your local `.env`; use the pooled URL as `DATABASE_URL` in Vercel.

Vercel’s local function filesystem is temporary. To keep uploaded images, configure a **private S3-compatible bucket** (for example, AWS S3 or Cloudflare R2) and set `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, and `AWS_ENDPOINT`. Set `AWS_USE_PATH_STYLE_ENDPOINT=true` if your provider requires it. Keep the bucket private: image downloads pass through an authenticated, ownership-checked Laravel route. If object storage is not configured, image uploads will not persist reliably on Vercel.

### 3. Deploy and check the app

Connect the GitHub repository, confirm the root directory and Node version, add the environment variables, then deploy. Visit `/up` to check Laravel’s health endpoint. If a request returns 500, open the project’s **Logs** in Vercel and inspect the matching function invocation; build logs do not contain request-time exceptions.

## Staff access and roles

The first staff account is created only by `AdminUserSeeder`; public registration always assigns the resident role. The seeder requires `ADMIN_EMAIL` and `ADMIN_PASSWORD`. Change staff credentials in the identity provider/database process, not by exposing credentials in source files.

## Security and storage notes

- `.env` contains secrets and must stay local. `.env.example` contains no credentials.
- Keep `APP_DEBUG=false` in production.
- Status changes are restricted to staff and can only advance one step at a time.
- Residents can view only their own requests and attached photos.
- Vercel does not provide durable local storage for uploaded files; configure private object storage for production uploads.
- Use HTTPS and a production database with backups.

## Tests

Run the feature suite with:

```powershell
php artisan test
```

The existing feature coverage exercises registration, authorization, request submission, image storage, and status transitions.
