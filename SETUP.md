# Base Site Template — build it once, clone it forever

This folder holds the **deployment files** that make a Laravel app deploy cleanly
on Coolify the same way ushpta does. The Laravel app itself you generate with
composer (below) — I can't ship a full framework tree, but these are the only
files that differ from a stock install.

## What this template IS

A clean, generic Laravel site with your standard wiring baked in, so every new
client starts from the same known-good base instead of scratch. It lives in
`D:\laragon\www\site-template`, is its own Git repo on GitHub, and the
provisioner points Coolify at it (or at a per-client copy).

## Build it once (do this with Claude Code in VS Code)

From `D:\laragon\www`:

```bash
# 1. Fresh Laravel
composer create-project laravel/laravel site-template
cd site-template

# 2. Frontend stack you use (Inertia + Vue + Tailwind)
composer require inertiajs/inertia-laravel tightenco/ziggy
npm install
npm install @inertiajs/vue3 vue@latest
npm install -D @vitejs/plugin-vue tailwindcss @tailwindcss/vite

# 3. Mail + storage drivers you use
composer require resend/resend-php league/flysystem-aws-s3-v3 "^3.0"
```

Then **drop the files from this folder into the project root**:
`Dockerfile`, `.dockerignore`, `.env.example` (overwrite Laravel's).

## The three things that make it Coolify-ready

1. **Dockerfile** — `serversideup/php:8.4-fpm-nginx`, same as ushpta. Builds
   vendor fresh, bakes the `public/storage` symlink for the uploads volume.
2. **Committed Vite assets** — your server has no Vite/npm. So the build output
   in `public/build/` **must be committed to Git**. Confirm `.gitignore` does
   NOT ignore `public/build`. Every time you change `.vue`/`.css`, run
   `npm run build` locally and commit the result before pushing.
3. **`.env.example`** — lists every key the provisioner injects plus the manual
   ones (Resend, Mapbox, MonCash). This is your checklist.

## Prove it once

```bash
npm run build            # produces public/build/*  → COMMIT these
git init && git add -A && git commit -m "Base site template"
# create a GitHub repo, push, and set its URL as TEMPLATE_GIT_REPO in launchpad/.env
```

Deploy it once via the provisioner to a throwaway subdomain and confirm the
whole loop works (build → deploy → SSL → loads). After that, it's your factory.

## Health check (optional but recommended)

Coolify can watch a health endpoint. Add to `routes/web.php`:

```php
Route::get('/up', fn () => response('ok', 200));   // Laravel 11 already has /up
```

Laravel 11 ships a `/up` route already — just point Coolify's health check at it.

## Per-client customization

For a bespoke client site, copy this template to a new repo
(`www\clientname`), customize, push, and point the provisioner at that repo
instead of the shared template. For near-identical sites, one template repo +
per-app env is enough.

## Optional shortcut

`stumason/laravel-coolify-starter` is a community package that scaffolds a
Coolify-ready Laravel app (health route, Postgres/Redis env, etc.). It leans on
Nixpacks and adds Horizon/Reverb/Telescope — heavier than your Dockerfile
approach, but worth a look if you want ideas. Your serversideup Dockerfile path
is leaner and matches what you already run.
