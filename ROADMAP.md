# ROADMAP — Portfolio

## Now
- [ ] First deploy (see DEPLOY.md "First deploy")
- [ ] Paste Upwork profile URL in Admin → Profile

## Next
- [ ] Ask clients who agree to be named; update client labels and enable live links
- [ ] Replace demo screenshots with fresh ones as apps change (Admin → project → Screenshots)
- [ ] Bring AskDoc back online on its own server and link it as a live demo

## Backlog
- [ ] Two-factor sign-in for the admin
- [ ] Short screen-recording clips (GIF/MP4) for PureOps and AskDoc

## Done
- [x] Site provisioned (repo + Coolify + DB + DNS)
- [x] Portfolio data model, admin backend, public pages, seeded content and screenshots

## Pending manual steps
- [ ] `php artisan migrate --force`
- [ ] `php artisan db:seed --class=PortfolioSeeder --force`
- [ ] `php artisan portfolio:admin <your email>`
