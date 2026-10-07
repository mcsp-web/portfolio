# CANON — [CLIENT NAME]

The living record for this site. Update it as you build. Newest entries at the
bottom of the log. This is what orients Claude Code (and you) instantly.

## Site facts
- **Client:** [name / business]
- **Domain:** [domain]
- **Repo:** mcsp-web/[repo-name]
- **Coolify project:** [project name/uuid]
- **Database:** shared Postgres → database `[db_name]`
- **Object storage:** [none | MinIO bucket `[bucket]`]
- **Provisioned on:** [date]

## Stack
Laravel 13 · Inertia · Vue 3 · Tailwind · Vite · Ziggy · PostgreSQL (shared) ·
Resend mail · Coolify deploy (serversideup Dockerfile, port 8080).

## Third-party keys this site uses (set in Coolify, NOT in repo)
- [ ] RESEND_API_KEY
- [ ] MAPBOX_ACCESS_TOKEN (if maps)
- [ ] MONCASH_CLIENT_ID / SECRET (if payments)
- [ ] AWS_* (if MinIO)

## Key decisions
- [date] — [decision and why]

## Known gotchas / notes
- Assets must be built locally and committed (`npm run build`) — server has no Vite.
- Migrations are manual on prod.

## Session log
- [date] — [what was done this session]
