# DEPLOY — Portfolio

How this site is deployed and run. Reference when something breaks.

## Infrastructure
- **Host:** Coolify on our VPS (IP [server address removed])
- **Coolify project:** [name/uuid]
- **App:** built from this repo's Dockerfile (serversideup/php:8.4-fpm-nginx, port 8080)
- **Database:** shared Postgres server → database `[db_name]`, user `[db_user]`
- **Storage:** [none | MinIO service `[name]`, bucket `[bucket]`]
- **Domain:** [domain] — Cloudflare (orange/proxied), SSL mode Full, Let's Encrypt on Coolify

## Deploy flow
1. Push to `main` → Coolify auto-builds and deploys.
2. Front-end changes require `npm run build` committed first (no Vite on server).
3. Migrations are manual: Coolify → app → Terminal → `php artisan migrate --force`.

## Environment variables (set in Coolify, not repo)
Generated automatically at provision time: APP_KEY, APP_URL, DB_*, (AWS_* if MinIO).
Set manually after provision: RESEND_API_KEY, MAPBOX_ACCESS_TOKEN, MONCASH_* (as needed).

## Common issues
- **New site 5xx for ~1 min:** SSL cert still issuing. Wait, then refresh.
- **Redirect loop:** Cloudflare SSL mode must be **Full** (not Flexible).
- **Assets 404 / old styles:** you forgot to `npm run build` + commit before pushing.
- **DB connection refused:** confirm this site's database exists on the shared Postgres
  and the DB_* env vars point at the shared server's internal host.

## Rollback
Coolify keeps previous deployments — use the app's Deployments tab to redeploy a
prior successful build.

## First deploy (portfolio)
1. Push to `main`; Coolify builds and deploys.
2. Coolify → app → Terminal:
   ```
   php artisan migrate --force
   php artisan db:seed --class=PortfolioSeeder --force
   php artisan portfolio:admin you@example.com
   ```
3. Sign in at `/admin/login`, open Profile, paste your Upwork URL, check every project.
4. Uploads: with no MinIO bucket, keep `PORTFOLIO_UPLOAD_DISK=public` and make sure the storage
   volume is mounted at `storage/app/public` so uploads survive redeploys.
5. On Upwork, link `https://<domain>/?via=upwork` (contact details removed for Upwork visitors).
