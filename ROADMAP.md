# ROADMAP — Portfolio

Derived from CANON.md. What's left, newest decisions first.

## Now (James, in the admin)
- [ ] Bank-statement tool: remove "currency conversion" from its summary/highlights (not switched on in the tool)
- [ ] Gmail filter so `[Portfolio]` enquiries land in Primary, not Updates (optional)
- [ ] Confirm a persistent volume is mounted at `storage/app/public` before uploading screenshots in the admin
- [ ] Add Fiverr profile URL in Admin → Profile if/when the Fiverr profile is live
- [ ] Submit https://portfolio.mcspconsulting.com/sitemap.xml in Google Search Console (optional)

## Next
- [ ] Ask clients who agree to be named; update client labels and turn on their live links
- [ ] Bring AskDoc back online on its own server and link it as a live demo
- [ ] Refresh screenshots as the apps change (Admin → project → Screenshots)

## Backlog
- [ ] Two-factor sign-in for the admin
- [ ] Short screen recordings (GIF/MP4) for PureOps and AskDoc
- [ ] Turn Launchpad back on for agency / hiring-manager audiences if useful

## Done
- [x] Site provisioned (repo, Coolify, database, DNS) and deployed
- [x] Data model, admin, public pages, 12 projects, sanitized screenshots
- [x] Upwork and Fiverr modes (contact details stripped server-side)
- [x] Admin login fixed behind the HTTPS proxy, with clear error messages
- [x] Full feature lists with hide/edit controls; headline numbers per project
- [x] Contact form, Admin → Messages, Resend email verified in production
- [x] Upwork profile link filled in
- [x] Launchpad hidden; broken-screenshot guard and cleanup
- [x] Link previews (Open Graph cards for home and each project), sitemap.xml, robots.txt

## Pending manual steps
- None. Migrations run on deploy; the seeder and admin account steps are done.
