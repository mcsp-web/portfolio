# CANON — Portfolio (James McCalla / MCSP Consulting)

The living record for this site. Newest log entries at the bottom.

## Site facts
- **What:** public portfolio of the apps and sites James has built, plus a private admin to manage it
- **Repo:** mcsp-web/portfolio
- **Database:** shared Postgres → its own database (tables: projects, technologies, project_technology, screenshots, settings)
- **Uploads:** `PORTFOLIO_UPLOAD_DISK` (`public` = app storage volume, `s3` = MinIO bucket)

## Stack
Laravel 13 · Inertia 3 · Vue 3 · Tailwind 4 · Vite · Ziggy · PostgreSQL (shared) · Coolify deploy
(serversideup Dockerfile, port 8080). Fonts self-hosted in public/fonts (Bricolage Grotesque, Figtree).

## How it works
- Public: `/` (hero, filterable work list, tech stack by layer with project counts, how I work, contact)
  and `/work/{slug}` (gallery with kind filter + lightbox, features, engineering notes, stack).
- Admin: `/admin` (login only, no registration). Projects: add/edit/delete, show/hide, feature,
  reorder, technologies; screenshots: upload, caption, kind, cover, reorder, remove. Tech stack and
  profile/links are editable too.
- Admin account: `php artisan portfolio:admin you@example.com` (prompts for a 12+ char password;
  rerun to reset it).
- Starting content: `PortfolioSeeder`. Re-runnable: never overwrites a project that exists, only
  adds new bundled screenshots from `public/images/projects/<slug>/shots.json`.
- **Upwork mode:** `/?via=upwork` strips email, LinkedIn, GitHub, website and live-site links for
  that visitor's session (server-side, not just hidden). Use this URL on Upwork, because Upwork's terms
  forbid sharing contact details before a contract. `?via=direct` turns it off.

## Screenshot policy
All bundled screenshots were taken from local copies of each app running on invented demo data:
fictional businesses/patients/customers, 555 phone numbers, @example addresses. Real client
contact details, staff names, bank details and photos of people were replaced before capture.
Client business names may appear on screenshots of their own websites; project `client_label`s
are generic until a client agrees to be named.

## Key decisions
- 2026-11-03 — Clients described generically by default (consent first).
- 2026-11-03 — Gym nutrition demo rebranded "Gym Nutrition" in screenshots; the gym's brand isn't shown.
- 2026-11-03 — Repos skipped: cehaiti (stub), smileclinic (superseded by sc1), booking-test and
  app-template (internal).

## Known gotchas
- Assets must be built locally and committed (`npm run build`); the server has no Vite.
- Migrations and seeding are manual on prod.

## Session log
- 2026-11-03 — Portfolio built from the site template: models, admin, public pages, 12 projects,
  sanitized screenshots, Upwork mode, 21 feature tests.
- 2026-11-03 — Admin login fix: app now trusts the Coolify/Cloudflare proxy (`trustProxies(at: '*')`)
  and forces https in production. Without it, login posts/redirects used http:// and errors never
  showed. Login page always shows a reason (wrong password, lockout after 5 tries for 60s, expired
  page, server unreachable).
- 2026-11-04 — Full feature lists: `features` table (group, name, detail, visible, order) plus
  `projects.stats` and `projects.show_feature_list`. 867 features across the 12 projects, written
  from each repo (built features only). Admin can hide/show/edit/remove a feature, hide/rename/reorder a
  group, or switch the whole list off. Seeder loads `database/seeders/features/<slug>.json` only into
  projects with no features yet, so re-running it never overwrites admin edits.
