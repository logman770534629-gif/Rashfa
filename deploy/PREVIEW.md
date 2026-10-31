# Rashfa: Vercel preview deployment

This configuration is for a temporary TEST/PREVIEW deployment only. It does not
authorize commercial production use of Vercel Hobby. Do not use `--prod`, promote
this deployment, purchase services, enable billing, create repositories, run
migrations or seeders, or create administrators as part of these instructions.

## Architecture and project settings

- Existing Laravel 11 / Blade application, served by FrankenPHP and Caddy.
- Vercel Container runtime (currently beta), using `Dockerfile.vercel`.
- Root Directory: `.`; Framework Preset: Container.
- Docker handles the build. Leave dashboard Install/Build/Output overrides unset.
- Node 22 builds Vite assets to `public/build` in a separate stage.
- PHP 8.4 and Composer install production dependencies from `composer.lock`.
- One `app` service routes every request to the container. Document root is
  `/app/public`, not the repository root.
- HTTP port: 80. Vercel provides external HTTPS. No Docker Compose or MySQL
  container is deployed.

Official references:

- https://vercel.com/docs/functions/runtimes/container
- https://vercel.com/kb/guide/laravel-php-with-docker

## Data protection and authentication

The default preview is read-only. `ProtectPreviewData` rejects unsafe web requests
except login and logout, before a controller can mutate content or upload files.
The MySQL connection starts with `SET SESSION TRANSACTION READ ONLY`. Use an
existing SELECT-only database account as an additional server-enforced safeguard;
creating a new database account requires the owner's approval.

Authentication now always requires a password; `RASHFA_ADMIN_OPEN` has no effect.
The login GET/POST routes are outside authenticated middleware. No visitor causes
automatic admin creation. Login/logout use cookie sessions, do not update admin
records or remember tokens, and do not require a sessions table or migrations.

Cookie sessions are encrypted/authenticated by Laravel. They are host-only,
Secure, HttpOnly and SameSite=Lax, with a distinct preview cookie name. Keep the
same private APP_KEY across redeployments of this preview. Do not share a production
APP_KEY with this preview. Cookie sessions have browser size limits: this read-only
preview stores only small auth/localization values. Review session size and
revocation requirements before any future write-enabled deployment.

Login throttling uses the configured cache. The preview uses files under `/tmp`
to avoid database writes. Limits persist per instance only and reset on recycling;
they are NOT a distributed brute-force defense. Keep Vercel preview authentication
enabled and use existing platform firewall protections. A shared cache or another
distributed limiter needs a separately approved existing/external service before
public production use. Account lock fields are respected but not modified.

## Required runtime configuration

Use `.env.example` only as a template. Never commit actual `.env` files, upload
them as source, put secret values in `vercel.json`, or pass secrets as Docker build
arguments. Add values to the Vercel **Preview** environment using the dashboard or
`vercel env add NAME preview`; do not print values in chat or terminal logs.

Required:

- `APP_KEY`: a private stable 32-byte Laravel encryption key.
- `DB_HOST`, `DB_PORT` (normally 3306), `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`:
  the existing MySQL database. Use SELECT-only privileges.
- `MYSQL_ATTR_SSL_CA`: trusted CA path; the image defaults to the system CA bundle.
- For a provider-specific public CA, `MYSQL_SSL_CA_BASE64`: base64-encoded PEM.
  The startup script writes it privately to `/tmp` and exports its path.
- `APP_URL`: HTTPS preview URL. If omitted, the startup script derives it from
  `VERCEL_URL`; expose Vercel system environment variables for this project.

A private `.env.preview.local` file has been prepared locally with a generated
APP_KEY and empty database fields. It is mode 0600 and excluded from Git/Docker/
Vercel source uploads. Populate it privately or add the required Preview variables
in Vercel. Do not source it as a shell script: dotenv values/passwords are not
necessarily valid shell syntax. Use an environment-file-aware loader.

The image supplies production/error/cookie settings and requires
`RASHFA_PREVIEW_READ_ONLY=true`, `SESSION_DRIVER=cookie`, `CACHE_STORE=file`,
`DB_CONNECTION=mysql`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`.
Do not set `DB_URL` for this preview. Startup validates settings without contacting
the database and refuses to start with missing credentials or unsafe overrides.

No full `config:cache` or `optimize` runs during the Docker build: runtime secrets
must not be captured into the image. Routes and Blade views are compiled during
the build. Runtime logs go to stderr; transient file cache uses `/tmp`.

## Database reachability

Obtain the current provider/host, MySQL version, TLS hostname/CA requirements and
firewall restrictions. `127.0.0.1`, Unix sockets and private LAN addresses are not
reachable from Vercel unless there is an approved networking solution. Do not open
MySQL indiscriminately to the internet. Container runtime currently lacks Static
IPs and Secure Compute, so a host requiring fixed egress allowlisting may block
this architecture. Place the function near the existing database after its region
is known. Do not relocate or change database engines to solve this automatically.

After credentials are supplied, `php deploy/check-database.php` performs SELECTs,
checks TLS and table readability, and prints only a generic result. It does not
run migrations or seeders. Run it with private environment injection; do not print
driver exception messages containing connection details. Repeat the check from
the actual Vercel runtime where supported, because local connectivity does not
prove Vercel reachability. The normal homepage is also database-backed; `/up` alone
is only an application health check and does not prove database connectivity.

## Media: pending existing host / service approval

No new storage service has been created. No existing files or database paths were
deleted or rewritten. Available `storage/app/public` files are copied into the
image's public `/storage` directory as immutable assets; logs and sessions are
excluded. The supplied copy contains thumbnail derivatives but no original media
under `public/media`. Obtain originals from the owner or existing host before
claiming visual completeness.

If an existing HTTPS host serves the same `/storage/...` and `/media/...` paths,
set `RASHFA_MEDIA_BASE_URL=https://existing-media-host.example`. The `media_url`
helper prefixes relative media URLs and preserves fully qualified URLs. Check all
rendered sources: raw/absolute legacy references may need compatibility redirects
or a separate approved correction. No database rewrite happens automatically.

Persistent writes cannot use the container filesystem. A future approved storage
integration must update both upload and delete paths, preserve existing URLs, and
use direct uploads for the application's 6 MiB images because Vercel Functions
document a 4.5 MB request-body limit. Do not silently reduce the upload feature or
enable uploads before this is resolved. The existing S3 disk config alone is not a
working integration: this lockfile lacks the S3 adapter. No provider/adapter was
selected without approval.

## Build and verify

Run these after network/package access is available:

```sh
composer install --prefer-dist --no-interaction --no-scripts
# Required for this supplied workspace: installed vendor files are incomplete.
composer reinstall '*' --prefer-dist --no-interaction --no-scripts
composer dump-autoload --optimize --no-interaction
npm ci --no-audit --no-fund
npm run build
vendor/bin/phpunit tests/Feature/PreviewSecurityTest.php
docker build -f Dockerfile.vercel -t rashfa-vercel-preview .
```

The security tests do not provision or connect to any database. They check guest
login, protected routes, read-only mutation rejection, viewer permissions, logout,
login limits, localization JSON and media-origin compatibility. Do not run the
old homepage example test against a production database. No `migrate`, `db:seed`,
`migrate:fresh` or admin creation command is part of the build/startup workflow.

To run the image locally, inject approved private runtime values with an ignored
environment file. Set `APP_URL=https://...` for the public preview. Local HTTP-only
browser authentication needs a separate local configuration and does not validate
Secure cookies; test those on the actual HTTPS preview.

## GitHub and Vercel

The owner supplied the existing repository:
`https://github.com/logman770534629-gif/Rashfa.git`.
This workspace has no usable Git metadata, and its `.git` path is read-only.
Inspect/clone the existing remote into a separate writable checkout when GitHub
network access is available. Compare remote history and files before applying the
prepared source. Do not create a new GitHub repository, overwrite remote files or
force-push. Use a preview branch without changing the production/default branch.
Hobby has restrictions on importing organization-owned repositories.

Once an existing Hobby project/account is identified, have the owner sign in and
authorize GitHub if needed. Do not use an anonymous fallback hosting service or
create additional projects/services without approval. Select the existing project
and root `.`, configure Preview variables, and deploy **only a Preview** with:

```sh
vercel link
vercel deploy
```

Do not use `--prod` or `-y` to accept unknown account/plan choices. With Git
integration, avoid deploying these changes to the configured production branch;
use an approved preview branch/PR after the repository is available. No GitHub
content has been changed by this preparation.

## Acceptance checks on the actual preview URL

- `/up`: application starts.
- `/`: database-backed homepage returns 200, with expected existing content.
- `/lang/en` and `/lang/ar`: HTML/JSON behavior and persistent localization.
- Login page: 200; dashboard as guest redirects to login without a loop.
- An existing active admin can sign in/out; invalid credentials do not authenticate.
- Mutating admin forms/uploads are rejected in this read-only preview.
- Secure/HttpOnly/SameSite cookies, HTTPS redirects and generated asset URLs.
- Nested admin pages, CSS/JS, source images and fallback images.
- No critical browser console, build or runtime errors.
- Database connection uses TLS and no existing records are modified.
- Repeat authentication after instance recycle/redeployment.

There is no registration feature in the existing app. Do not add registration or
test write operations against existing records. Keep the deployment marked as
temporary preview. A URL alone, a passing build or `/up` alone is not success.
