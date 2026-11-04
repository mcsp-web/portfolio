# CANON — Portfolio (James McCalla / MCSP Consulting)

The living record for this site: what it is, how it works, and why. Newest log entries at the
bottom. ROADMAP.md is derived from this file.

## Site facts
- **What:** public portfolio of the apps and sites James has built, with a private admin to manage it
- **Live:** https://portfolio.mcspconsulting.com (admin at `/admin`)
- **Repo:** mcsp-web/portfolio, branch `main`; Coolify auto-deploys on push
- **Database:** shared Postgres, own database. Tables: `projects`, `technologies`,
  `project_technology`, `screenshots`, `features`, `settings`, `messages`, plus Laravel's
  `users`, `sessions`, `cache`, `jobs`
- **Mail:** Resend. From `portfolio@mcspconsulting.com` ("MCSP Portfolio"), same API key as the
  mcspconsulting app; enquiries go to the profile contact email (info@mcspconsulting.com)
- **Uploads:** `PORTFOLIO_UPLOAD_DISK` (`public` = app storage volume, `s3` = MinIO bucket)

## Stack
Laravel 13 · Inertia 3 · Vue 3 · Tailwind 4 · Vite · Ziggy · PostgreSQL (shared) · Resend ·
Coolify deploy (serversideup php 8.4 Dockerfile, port 8080, behind Cloudflare).
Fonts self-hosted in `public/fonts` (Bricolage Grotesque for display, Figtree for text).
Inertia page path is `resources/js/Pages` (set in `config/inertia.php`; the package default is lowercase).

## How it works
### Public site
- `/` — hero with fanned real screenshots, projects/technologies counts, filterable work list
  (featured projects as large rows, the rest as a compact list), tech stack by layer with a
  per-technology project count, "How I work", contact section.
- `/work/{slug}` — headline numbers (feature count + per-project stats), screenshot gallery
  (filter by public / back office / mobile, lightbox, tall images cropped then scrollable), summary,
  "What it does", "Under the hood", "Built with", and a collapsible **Full feature list**.
- Only published projects appear; hidden ones return 404. Live-site links show only when allowed.
- Bundled screenshots whose file is missing are skipped, never shown broken.

### Link previews and search
- Server-rendered tags (title, description, canonical, Open Graph, Twitter card) on `/` and every
  project page, because preview bots (LinkedIn, WhatsApp, Slack, X, iMessage) don't run JavaScript.
- Preview images: `public/images/og/<slug>.jpg` (1200x630, home + one per project), made by the
  card generator script from the site fonts, colours and each project's cover screenshot. A project
  without its own card falls back to the home card. The home card's headline is baked into the image:
  regenerate it if the profile headline changes.
- `og:url` and canonical never include `?via=upwork`, so shared links stay clean.
- `/sitemap.xml` (home + published projects) and `/robots.txt` (blocks `/admin`, points to the sitemap);
  admin pages also carry `noindex`. `apple-touch-icon.png` for phone home screens.

### Marketplace mode (Upwork / Fiverr)
- `/?via=upwork` or `/?via=fiverr` puts that visitor's session in marketplace mode; `?via=direct` ends it.
- In marketplace mode the server strips email, LinkedIn, GitHub, website and live-site links, the
  contact form is hidden and `POST /contact` returns 404. The only call to action is
  "Message me on Upwork/Fiverr" (profile link). Reason: both platforms forbid sharing contact
  details before a contract, including on a linked website.
- Always share the site on Upwork as `https://portfolio.mcspconsulting.com/?via=upwork`.

### Contact form
- Name, email, company, project type, budget, message. Honeypot field; 3 messages per hour per IP.
- Saved to `messages` first, then emailed (reply-to = sender) with subject
  `[Portfolio] New enquiry from <name>` and header `X-Source: portfolio.mcspconsulting.com`.
  A mail failure never loses a message; it shows as "Saved only" in the admin.
- Admin → Messages: list, reply by email, mark read/unread, delete; unread count in the menu.

### Admin (`/admin`, login only)
- Sign-in shows a reason on every failure; 5 wrong tries lock that email+IP for 60 seconds.
- Projects: add, edit, delete, show/hide, feature, reorder, technologies, headline numbers
  (`number | label` per line), screenshots (upload, caption, kind, cover, reorder, remove).
- Full feature list per project: hide/show/edit/remove a feature, add new ones, hide/show/rename/
  reorder a whole group, or switch the project's list off.
- Tech stack (name, layer, show on profile) and Profile (name, headline, intro, about,
  availability, location, email, Upwork/Fiverr/LinkedIn/GitHub/website links).
- Admin account: `php artisan portfolio:admin you@example.com` (prompts for a 12+ character
  password; rerun with the same email to reset it, a new email adds a second admin).

### Content loading
- `PortfolioSeeder` creates the 12 projects, 68 technologies, profile defaults, bundled screenshots
  (`public/images/projects/<slug>/shots.json`) and feature lists (`database/seeders/features/<slug>.json`).
- Re-runnable and non-destructive: existing projects are never overwritten, features load only into
  projects that have none, headline numbers only when empty, and only new bundled screenshots are added.

## Current content (2026-11-04)
- 12 projects; Launchpad hidden by choice (11 public). 84 screenshots, 865 features, 68 technologies.
- Projects: PureOps, dermatology telehealth app, AskDoc, Launchpad (hidden), MCSP platform,
  small-business website platform, detailing booking site, dental clinic site, process-server site,
  PTA website, gym nutrition app, bank-statement bookkeeping tool.

## Screenshot policy
All bundled screenshots come from local copies of each app running on invented demo data
(fictional businesses, patients and customers; 555 numbers; @example addresses). Real client contact
details, staff names, bank/Zelle details and photos of people were replaced before capture, and every
image was OCR-scanned for leaks. Client business names may appear on screenshots of their own
websites; project client labels stay generic until a client agrees to be named.

## Key decisions
- 2026-11-03 — Clients described generically by default (consent first).
- 2026-11-03 — Gym demo shown as "Gym Nutrition"; the gym's brand isn't used.
- 2026-11-03 — Repos left out: cehaiti (stub), smileclinic (replaced by sc1), booking-test and
  app-template (internal).
- 2026-11-04 — Feature lists: headline highlights stay short; the full inventory sits in a collapsed,
  grouped section. Only features present in code are listed (nothing planned or spec-only).
- 2026-11-04 — Contact form allowed for normal visitors only; marketplace visitors get the
  marketplace profile link instead (platform rules).
- 2026-11-04 — Launchpad hidden so prospects don't read client sites as "made in five minutes";
  its Launchpad screenshot was also removed from the website platform project.

## Known gotchas
- Assets must be built locally and committed (`npm run build`); the server has no Vite.
- Migrations run automatically on deploy (`AUTORUN_ENABLED=true` in the Dockerfile). Seeding does not.
- The app must trust the proxy (`trustProxies(at: '*')`) and force https in production, or login
  posts and redirects break behind Cloudflare/Traefik.
- Env vars in Coolify must not be marked build-only (the Resend key was empty at runtime until fixed).
- Uploaded screenshots (`public` disk) live on the Coolify volume `portfolio-uploads`, mounted at
  `/var/www/html/storage/app/public` (added 2026-11-04, verified across a redeploy). Remove it and
  uploads are lost on every deploy.

## Session log
- 2026-11-03 — Portfolio built from the site template: models, admin, public pages, 12 projects,
  sanitized screenshots, Upwork mode.
- 2026-11-03 — Fiverr mode and Fiverr profile link; gallery handles tall screenshots.
- 2026-11-03 — Admin login fix: trust the proxy, force https in production; login always shows a
  reason (wrong password, lockout, expired page, server unreachable). Password-reset hint removed
  from the login page at James's request.
- 2026-11-04 — Full feature lists (`features` table, `projects.stats`, `projects.show_feature_list`):
  865 features across 12 projects from repo inventories, with admin hide/edit controls.
- 2026-11-04 — Contact form + Admin → Messages; Upwork profile link filled in by migration.
  Resend wired and verified in production (test email received).
- 2026-11-04 — Emails tagged `[Portfolio]` with an `X-Source` header; sender "MCSP Portfolio".
- 2026-11-04 — Launchpad hidden; its screenshot removed from the website platform project; missing
  bundled screenshots are skipped and cleaned up by migration. 42 tests passing.
- 2026-11-04 — Link previews: server-side Open Graph/Twitter tags, designed 1200x630 cards for home
  and each project, sitemap.xml, robots.txt, admin noindex, apple-touch-icon. 47 tests passing.
- 2026-11-04 — Persistent volume `portfolio-uploads` added in Coolify at `storage/app/public`;
  a test file survived a redeploy into a new container.
