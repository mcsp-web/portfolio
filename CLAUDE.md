# CLAUDE.md — instructions for Claude Code

You are working in a client website repo generated from `mcsp-web/site-template`.
Read this file first, every session. It defines how we build and ship here.

## Stack (do not change without noting it in CANON.md)
- **Laravel** (13.x) · **Inertia.js** · **Vue 3** (Composition API) · **Tailwind CSS** · **Vite** · **Ziggy**
- **PostgreSQL** (shared server, per-site database)
- **Mail:** Resend · **Storage:** S3/MinIO (only if this site needs uploads)
- **Deploy:** Coolify on our VPS, built from this repo's **Dockerfile** (serversideup/php:8.4-fpm-nginx)

## The non-negotiable rules
1. **Build assets before every commit that touches `.vue`/`.css`/`.js`.**
   The production server has NO Vite/npm. It serves what's committed.
   → Run `npm run build`, then commit the `public/build/` output. Never skip this.
2. **`public/build/` MUST be committed.** If `.gitignore` ignores it, remove that line.
3. **Migrations are MANUAL on production.** Never assume auto-migrate on deploy.
   Write migrations, but run them via the Coolify container terminal:
   `php artisan migrate --force`. Note any pending migration in ROADMAP.md.
4. **Secrets live in Coolify env vars, never in the repo.** `.env` is git-ignored.
   `.env.example` is the source of truth for what keys exist.
5. **Ask before adding heavy dependencies** (queues/Horizon/Reverb/websockets).
   This box is RAM-constrained; every service costs memory.

## Workflow for a task
1. Read CANON.md (context/decisions) and ROADMAP.md (what's in flight).
2. Make the change. Keep components small and readable.
3. If front-end changed: `npm run build`.
4. Commit with a clear message. Push (Coolify auto-deploys on push).
5. Update CANON.md (what changed + why) and ROADMAP.md (move the item to done).
6. If a migration was added, flag it as a manual prod step.

## Deploy facts
- Coolify pulls this repo and builds the Dockerfile. Port exposed: **8080**.
- The `public/storage` symlink is baked in the Dockerfile for the uploads volume.
- SSL is automatic (Let's Encrypt via Coolify/Traefik). Domain proxied via Cloudflare.
- First deploy of a new site may briefly 5xx while the cert issues — wait ~1 min.

## What NOT to do
- Don't commit `.env` or any secret.
- Don't run destructive DB commands against production.
- Don't add a per-site database server — we use the shared Postgres.
- Don't introduce a build step that assumes the server can run npm.
