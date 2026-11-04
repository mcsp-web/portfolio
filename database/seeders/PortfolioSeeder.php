<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Project;
use App\Models\Screenshot;
use App\Models\Setting;
use App\Models\Technology;
use Illuminate\Database\Seeder;

/**
 * Loads the starting portfolio: technologies, projects and the bundled screenshots in
 * public/images/projects/<slug>/ (described by each folder's shots.json).
 *
 * Safe to re-run: projects that already exist are left alone, so edits made in the admin
 * are never overwritten. Only new bundled screenshots are added.
 */
class PortfolioSeeder extends Seeder
{
    /** @var array<string, list<string>> */
    private const TECH = [
        'frontend' => ['Vue 3', 'Inertia.js', 'Tailwind CSS', 'JavaScript', 'TypeScript', 'Vite', 'Alpine.js', 'Blade', 'HTML & CSS', 'Progressive Web Apps', 'Leaflet', 'Capacitor (iOS & Android)', 'vue-i18n'],
        'backend' => ['Laravel', 'PHP', 'Python', 'FastAPI', 'Node.js', 'REST APIs', 'Queues & scheduled jobs', 'Supabase Edge Functions'],
        'data' => ['PostgreSQL', 'SQLite', 'MySQL', 'Supabase', 'Row-level security', 'S3 / MinIO storage', 'NumPy'],
        'ai' => ['Claude API (Anthropic)', 'OpenAI API', 'Retrieval-augmented generation (RAG)', 'sentence-transformers embeddings', 'Gemini image generation', 'AI agents & tool use'],
        'infra' => ['Docker', 'Coolify', 'Hetzner VPS', 'Cloudflare', 'Cloudflare Tunnel & Zero Trust', 'GitHub Actions', 'GitHub Container Registry', 'Vercel', 'Nginx', 'Linux server admin'],
        'integration' => ['Stripe', 'Stripe Connect', 'Resend email', 'Mapbox', 'Google Places API', 'WhatsApp Cloud API', 'Daily.co video', 'Firebase Cloud Messaging', 'Web Push', 'Meta Graph API', 'GitHub API', 'Cloudflare API', 'Open-Meteo', 'US Census geocoder', 'iCalendar (ICS) feeds', 'Zoho Mail API', 'PDF generation (dompdf)'],
        'testing' => ['PHPUnit', 'Playwright', 'Vitest', 'Larastan / PHPStan', 'Laravel Pint', 'ESLint & Prettier', 'TOTP two-factor auth'],
    ];

    public function run(): void
    {
        $order = 0;
        foreach (self::TECH as $layer => $names) {
            foreach ($names as $name) {
                Technology::firstOrCreate(['name' => $name], ['layer' => $layer, 'sort_order' => $order++]);
            }
        }

        foreach (Setting::DEFAULTS as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        foreach ($this->projects() as $i => $data) {
            $tech = $data['tech'];
            unset($data['tech']);

            $project = Project::firstOrCreate(['slug' => $data['slug']], $data + ['sort_order' => $i]);
            if ($project->wasRecentlyCreated) {
                $ids = Technology::whereIn('name', $tech)->pluck('id');
                $project->technologies()->sync($ids);
            }

            $this->attachBundledScreenshots($project);
            $this->attachFeatureList($project);
        }
    }

    /**
     * Load database/seeders/features/<slug>.json into a project that has no features yet,
     * and its headline numbers if none are set. Never touches a list edited in the admin.
     */
    private function attachFeatureList(Project $project): void
    {
        $file = database_path("seeders/features/{$project->slug}.json");
        if (! is_file($file)) {
            return;
        }

        $data = json_decode((string) file_get_contents($file), true) ?: [];

        if (empty($project->stats) && ! empty($data['stats'])) {
            $project->update(['stats' => array_values($data['stats'])]);
        }

        if ($project->features()->exists()) {
            return;
        }

        $order = 0;
        $rows = [];
        foreach ($data['groups'] ?? [] as $group) {
            foreach ($group['features'] ?? [] as $feature) {
                $rows[] = [
                    'project_id' => $project->id,
                    'group_name' => mb_substr((string) $group['name'], 0, 80),
                    'name' => mb_substr((string) $feature['name'], 0, 160),
                    'detail' => isset($feature['detail']) && $feature['detail'] !== '' ? mb_substr((string) $feature['detail'], 0, 300) : null,
                    'is_visible' => true,
                    'sort_order' => $order++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            Feature::insert($chunk);
        }
    }

    private function attachBundledScreenshots(Project $project): void
    {
        $dir = public_path("images/projects/{$project->slug}");
        $manifest = $dir.'/shots.json';
        if (! is_file($manifest)) {
            return;
        }

        $shots = json_decode((string) file_get_contents($manifest), true) ?: [];
        foreach ($shots as $i => $shot) {
            $path = "{$project->slug}/{$shot['file']}";
            if (! is_file(public_path('images/projects/'.$path))) {
                continue;
            }
            Screenshot::firstOrCreate(
                ['project_id' => $project->id, 'disk' => 'bundled', 'path' => $path],
                [
                    'caption' => $shot['caption'] ?? null,
                    'kind' => $shot['kind'] ?? 'public',
                    'is_cover' => (bool) ($shot['cover'] ?? false),
                    'sort_order' => $i,
                ],
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function projects(): array
    {
        return [
            [
                'slug' => 'pureops',
                'title' => 'PureOps',
                'tagline' => 'Field-service management for small service businesses',
                'category' => 'saas',
                'status' => 'in_development',
                'client_label' => 'My own SaaS product',
                'role' => 'Founder: product, design, full-stack build, DevOps',
                'year' => 2026,
                'is_featured' => true,
                'live_url' => 'https://pureops.app',
                'show_live_url' => true,
                'summary' => 'A multi-tenant platform that runs the day-to-day of a service business: leads and quotes, scheduling and dispatch, a phone-first technician app that works offline, invoicing and online payments, inventory and fleet.',
                'body' => "PureOps started as software for a pest-control company and grew into a platform for seven trades, including lawn care, HVAC, fuel delivery and mobile car detailing. Each business gets its own workspace, with labels, default services and job fields that match its industry.\n\nOffice staff schedule and dispatch from a desktop board; technicians work from an installable phone app that keeps working without signal and syncs when it reconnects. Customers get their own portal and an online booking page.",
                'highlights' => [
                    'Multi-tenant workspaces with eight staff roles, per-tenant feature switches and plan-based limits',
                    'Scheduling board with day, week, month and list views, drive times and route optimization',
                    'Offline-capable technician app with photos, signatures, proof of delivery and timesheets',
                    'Live technician map with location trails snapped to roads',
                    'Quotes customers accept online, then invoices with PDF and QR codes, paid by card through Stripe',
                    'Customer portal with magic-link sign-in and a public booking page with real-time open slots',
                    'Inventory, truck and fuel tracking with reconciliation and custody checks',
                    'Self-serve sign-up, setup wizard, guided tours and spreadsheet client import with undo',
                ],
                'engineering' => [
                    'Over 1,300 automated tests across 192 PHPUnit files, plus 27 Playwright end-to-end suites including mobile and offline runs',
                    'An automated test proves one business can never see another business’s data',
                    'GitHub Actions runs the full suite against a fresh PostgreSQL 17 database on every push',
                    'Booking uses a PostgreSQL advisory lock so two customers can never take the same slot',
                    'Append-only ledgers for stock movements, fuel custody and audit history',
                    'About 40 service classes, 60 models and 185 migrations, with industries, features and plans defined in configuration',
                    'Two-factor sign-in, login rate limits, signed URLs and logged, permission-based support access',
                ],
                'tech' => ['Laravel', 'PHP', 'Vue 3', 'Inertia.js', 'Tailwind CSS', 'Vite', 'PostgreSQL', 'Progressive Web Apps', 'Leaflet', 'vue-i18n', 'Queues & scheduled jobs', 'Stripe', 'Stripe Connect', 'Mapbox', 'Resend email', 'Open-Meteo', 'US Census geocoder', 'S3 / MinIO storage', 'PDF generation (dompdf)', 'PHPUnit', 'Playwright', 'Larastan / PHPStan', 'Laravel Pint', 'ESLint & Prettier', 'TOTP two-factor auth', 'GitHub Actions', 'Docker', 'Coolify', 'Hetzner VPS', 'Cloudflare'],
            ],
            [
                'slug' => 'telehealth-app',
                'title' => 'Dermatology telehealth app',
                'tagline' => 'Patient intake, booking and video visits for a skin clinic',
                'category' => 'saas',
                'status' => 'in_development',
                'client_label' => 'Dermatology practice, Haiti',
                'role' => 'Full-stack build, mobile packaging, hosting',
                'year' => 2026,
                'is_featured' => true,
                'summary' => 'An installable web app, also packaged for Android and iOS, that connects patients with their dermatologist: health history, photo uploads, appointments, video consultations and billing, with separate workspaces for the doctor, reception and administration.',
                'body' => "Patients sign in with a one-time code sent by email or WhatsApp, complete a structured health profile and upload photos of the area of concern. They book a slot, join the video visit from their phone and settle their bill.\n\nThe doctor works from a patient list with a structured consultation form and full visit history. Reception handles intake and invitations; administrators manage accounts, the payment journal and staff access. The app runs in French, Haitian Creole and English.",
                'highlights' => [
                    'Passwordless patient sign-in by email or WhatsApp code',
                    'Structured health history and secure photo uploads',
                    'Appointment booking against the doctor’s hours and days off',
                    'Video consultations from any phone or browser',
                    'Doctor, reception, administration and accounting workspaces with role-based access',
                    'Payment requests and a ledger for manual and bank payments',
                    'Help centre with articles, plus push notifications on Android and iOS',
                ],
                'engineering' => [
                    '476 automated tests across 39 migrations',
                    'Staff two-factor sign-in, a staff audit log and signed confidentiality agreements',
                    'Medical files stored in private object storage, never on a public URL',
                    'One codebase served as a PWA and wrapped with Capacitor for the app stores',
                ],
                'tech' => ['Laravel', 'PHP', 'Vue 3', 'Inertia.js', 'Tailwind CSS', 'Vite', 'PostgreSQL', 'Progressive Web Apps', 'Capacitor (iOS & Android)', 'Daily.co video', 'WhatsApp Cloud API', 'Firebase Cloud Messaging', 'Resend email', 'S3 / MinIO storage', 'TOTP two-factor auth', 'PHPUnit', 'Docker', 'Coolify'],
            ],
            [
                'slug' => 'askdoc',
                'title' => 'AskDoc',
                'tagline' => 'Ask questions of any document and get answers with page citations',
                'category' => 'ai',
                'status' => 'demo',
                'client_label' => 'Demo for AI-integration clients',
                'role' => 'Design, retrieval pipeline, API, deployment',
                'year' => 2026,
                'is_featured' => true,
                'summary' => 'Upload a PDF, Word, text or Markdown file and ask questions in plain language. Answers come only from that document and show the exact passages and page numbers they came from, so every answer can be checked.',
                'body' => "AskDoc is a retrieval-augmented generation (RAG) app built without a framework like LangChain, so every step is visible and tunable. The document is split into overlapping passages and embedded on the server with a local model; a question pulls back the closest passages, and only those are sent to the language model with instructions to answer from them or say it couldn't find the answer.\n\nThe live demo is paused to save server resources. These screenshots use a sample employee handbook.",
                'highlights' => [
                    'Accepts PDF, Word (including tables), text and Markdown',
                    'Answers grounded only in the uploaded document, with a clear “not found” instead of guessing',
                    'Every answer lists its source passages with page numbers and match scores',
                    'Switch between Claude and OpenAI with a setting, no code change',
                    'Clear errors for unsupported or oversized files',
                ],
                'engineering' => [
                    'Custom retrieval pipeline: 900-character chunks with overlap, MiniLM embeddings, cosine similarity in NumPy',
                    'Search runs locally on the server at no per-query cost; only the final answer calls the AI model',
                    'The index is swapped in atomically, so a failed upload never leaves a half-built index',
                    'Docker image built and tested in GitHub Actions, published to GHCR and deployed by tag with one-step rollback',
                ],
                'tech' => ['Python', 'FastAPI', 'NumPy', 'Claude API (Anthropic)', 'OpenAI API', 'Retrieval-augmented generation (RAG)', 'sentence-transformers embeddings', 'HTML & CSS', 'JavaScript', 'Docker', 'GitHub Actions', 'GitHub Container Registry', 'Coolify', 'Cloudflare'],
            ],
            [
                'slug' => 'launchpad',
                'title' => 'Launchpad',
                'tagline' => 'A one-click website factory for a web design studio',
                'category' => 'tool',
                'status' => 'internal',
                'client_label' => 'Internal tool for MCSP Consulting',
                'role' => 'Design, build, infrastructure',
                'year' => 2026,
                'is_featured' => true,
                'summary' => 'Enter a client’s name and domain and Launchpad builds the whole stack: a private code repository from a template, a database, DNS records and a deployed app with SSL, live in minutes. It also drafts site copy with AI and produces design proposals for prospects.',
                'body' => "Launchpad turned a two-hour manual setup into a single form. It talks to the hosting panel, Cloudflare, GitHub and the database server through small, tested API wrappers, and records every site it creates so it can tear one down safely later.\n\nAround that core it grew into a small studio: collect a client brief (or import it from their old website or Google listing), have Claude draft the copy in several languages, preview three designs, send a proposal, find local businesses without a website, and schedule social posts.",
                'highlights' => [
                    'One-click provisioning: repository, database, DNS, hosting app, environment and first deploy',
                    'Guarded teardown that shows its plan first and requires typing the site name to confirm',
                    'Client briefs for US and Haiti markets in English, Spanish, French and Haitian Creole',
                    'AI-drafted site copy and live previews across nine industry layouts',
                    'Prospect search for local businesses with no website, using the Google Places API',
                    'Content studio for social posts with AI images and blog articles',
                ],
                'engineering' => [
                    '393 automated checks that run with no network, using local fake servers for every outside API',
                    'Server-side fetcher that blocks private addresses and re-checks every redirect (SSRF protection)',
                    'Allow-list router, fail-closed admin password and Cloudflare Zero Trust in front',
                    'The step most likely to fail runs first, so a failed setup leaves nothing behind',
                ],
                'tech' => ['PHP', 'JavaScript', 'HTML & CSS', 'PostgreSQL', 'REST APIs', 'Claude API (Anthropic)', 'Gemini image generation', 'Google Places API', 'GitHub API', 'Cloudflare API', 'Meta Graph API', 'Resend email', 'Zoho Mail API', 'Docker', 'Coolify', 'Cloudflare Tunnel & Zero Trust', 'Hetzner VPS', 'Linux server admin'],
            ],
            [
                'slug' => 'mcsp-platform',
                'title' => 'MCSP Consulting platform',
                'tagline' => 'Agency website, sales pipeline and online proposals in one app',
                'category' => 'saas',
                'status' => 'live',
                'client_label' => 'MCSP Consulting (my company)',
                'role' => 'Design, full-stack build, hosting',
                'year' => 2026,
                'is_featured' => true,
                'live_url' => 'https://mcspconsulting.com',
                'show_live_url' => true,
                'summary' => 'A trilingual marketing site backed by a full business console: prospects and leads, proposals that clients sign and pay for online, client records, a payments ledger, an AI sales assistant and a referral-partner portal.',
                'body' => "The public site sells website builds, care plans, AI assistants and custom apps in English, French and Spanish. Behind it, the admin console runs the whole sales cycle: research a prospect, send an outreach sequence, turn the lead into a proposal, and let the client review, e-sign and pay by card from a private link.\n\nReferral partners get their own portal with marketing material, AI coaching for sales calls, commissions and payout requests.",
                'highlights' => [
                    'Marketing site in English, French and Spanish with structured data for search',
                    'Proposals clients accept, sign and pay online, with a signed PDF kept on file',
                    'Admin console for prospects, leads, proposals, clients and a Stripe payments ledger',
                    'On-site AI sales assistant and AI-assisted prospect research, built on Claude',
                    'Partner portal with commissions, tax forms and payouts',
                    'Admin access requires two-factor sign-in',
                ],
                'engineering' => [
                    '146 PHPUnit test files and 48 Vitest tests',
                    'Money stored as integer cents and totals computed in one place',
                    'Each signed agreement keeps a snapshot of the proposal and its SHA-256 hash',
                    'AI usage cost tracked per call in micro-dollars',
                    'A test fails if the three languages drift out of step',
                ],
                'tech' => ['Laravel', 'PHP', 'Vue 3', 'Inertia.js', 'Tailwind CSS', 'Vite', 'PostgreSQL', 'Claude API (Anthropic)', 'AI agents & tool use', 'Stripe', 'Resend email', 'PDF generation (dompdf)', 'TOTP two-factor auth', 'Queues & scheduled jobs', 'PHPUnit', 'Vitest', 'Docker', 'Coolify', 'Cloudflare'],
            ],
            [
                'slug' => 'website-platform',
                'title' => 'Small-business website platform',
                'tagline' => 'One content file becomes a fast, bookable business website',
                'category' => 'saas',
                'status' => 'live',
                'client_label' => 'Product behind MCSP client sites',
                'role' => 'Design, full-stack build',
                'year' => 2026,
                'summary' => 'The template every client site starts from. Nine industry layouts, online booking with per-staff calendars, an installable owner app with push notifications, owner editing of words and photos, and an optional AI assistant that answers visitors and captures leads.',
                'highlights' => [
                    'Nine industry layouts with alternative designs, all driven by one content file',
                    'Booking by request or instant confirmation, with staff calendars, time off and calendar feeds',
                    'Owner app installable on a phone, with sign-in by emailed link or code',
                    'Push notifications for new bookings and cancellations',
                    'Owners edit text, photos and news posts with preview, history and restore',
                    'Optional AI assistant with rate and monthly cost limits',
                ],
                'engineering' => [
                    'Web Push implemented from scratch without a library',
                    'Photos resized and stripped of location data on upload',
                    'Services import from CSV or Excel with a review step before saving',
                ],
                'tech' => ['Laravel', 'PHP', 'Vue 3', 'Inertia.js', 'Tailwind CSS', 'Vite', 'PostgreSQL', 'Progressive Web Apps', 'Web Push', 'Claude API (Anthropic)', 'iCalendar (ICS) feeds', 'Resend email', 'PHPUnit', 'Docker', 'Coolify'],
            ],
            [
                'slug' => 'detailing-booking',
                'title' => 'Mobile detailing booking site',
                'tagline' => 'Book and pay for a car detail online, any time',
                'category' => 'client_site',
                'status' => 'in_development',
                'client_label' => 'Mobile auto-detailing business, Miami',
                'role' => 'Design, full-stack build, hosting',
                'year' => 2026,
                'summary' => 'A bilingual site where customers choose a package, vehicle size and time slot, add extras or a promo code, and pay by card. The owner runs bookings, payments, refunds, promo codes and membership plans from a phone-installable back office.',
                'highlights' => [
                    'Five-step booking flow with upsells, promo codes and gift cards',
                    'Card payments, refunds and payment links through Stripe',
                    'Membership plans with visit credits',
                    'Owner back office: calendar, customers, service menu, hours, days off and policies',
                    'Address and phone checked as the customer types; returning customers recognised',
                    'English and Spanish',
                ],
                'engineering' => [
                    '199 automated tests',
                    'Stripe called directly through its REST API with verified webhooks',
                ],
                'tech' => ['Laravel', 'PHP', 'Vue 3', 'Inertia.js', 'Tailwind CSS', 'PostgreSQL', 'Stripe', 'Resend email', 'Web Push', 'US Census geocoder', 'Progressive Web Apps', 'PHPUnit', 'Docker', 'Coolify'],
            ],
            [
                'slug' => 'dental-clinic',
                'title' => 'Dental clinic website and booking',
                'tagline' => 'Trilingual clinic site with appointment requests per dentist',
                'category' => 'client_site',
                'status' => 'in_development',
                'client_label' => 'Dental clinic, Pétion-Ville, Haiti',
                'role' => 'Design, full-stack build, hosting',
                'year' => 2026,
                'summary' => 'A multi-page site in French, English and Spanish with services, team, patient information and a dental tips blog. Patients request appointments with a chosen dentist around the clock; front-desk staff confirm from a phone-installable back office.',
                'highlights' => [
                    'Three languages throughout, including the blog',
                    'Appointment requests per dentist, confirmed by the clinic',
                    'Calendar feed for each dentist',
                    'Back office for bookings, services (with spreadsheet import), hours, staff and the public team page',
                    'Push alerts to the front desk',
                ],
                'engineering' => ['76 automated tests', 'Search-friendly structured data and sitemap'],
                'tech' => ['Laravel', 'PHP', 'Vue 3', 'Inertia.js', 'Tailwind CSS', 'PostgreSQL', 'iCalendar (ICS) feeds', 'Web Push', 'Resend email', 'PHPUnit', 'Docker', 'Coolify'],
            ],
            [
                'slug' => 'process-server-site',
                'title' => 'Process-server website',
                'tagline' => 'Trilingual marketing site with inquiries, reviews and a secure admin',
                'category' => 'client_site',
                'status' => 'in_development',
                'client_label' => 'Process-serving company, Miami-Dade',
                'role' => 'Design, full-stack build, hosting',
                'year' => 2026,
                'summary' => 'A site in English, Spanish and Haitian Creole for a legal process-serving company, with a page for each service and each audience (attorneys, landlords, individuals), pricing, an FAQ and an inquiry form. The owner manages inquiries, reviews and settings from a two-factor admin.',
                'highlights' => [
                    'Service and audience pages in three languages',
                    'Inquiry form with confirmation emails',
                    'Customer reviews with moderation',
                    'Admin with two-factor sign-in and recovery codes',
                    'FAQ structured data for search results',
                ],
                'engineering' => ['304 automated tests', 'Server-rendered Blade with Alpine.js for a light, fast front end'],
                'tech' => ['Laravel', 'PHP', 'Blade', 'Alpine.js', 'Tailwind CSS', 'PostgreSQL', 'Resend email', 'TOTP two-factor auth', 'PHPUnit', 'Docker', 'Coolify', 'Cloudflare'],
            ],
            [
                'slug' => 'pta-website',
                'title' => 'School PTA website',
                'tagline' => 'Public site, member sign-up and an officers’ back office',
                'category' => 'client_site',
                'status' => 'live',
                'client_label' => 'Parent-teacher association, Haiti',
                'role' => 'Design, full-stack build, hosting',
                'year' => 2026,
                'live_url' => 'https://ushpta.com',
                'show_live_url' => false,
                'summary' => 'Parents join through a protected sign-up form, register their children and choose volunteer committees. Officers manage the roster, committees, board, event galleries and a school-year calendar that families can subscribe to on their phones.',
                'highlights' => [
                    'Access-code sign-up for parents and community members',
                    'Roster with CSV export and per-committee lists',
                    'Calendar with public and officers-only views and a subscribe feed',
                    'Managers for board members, committees, highlights with photo galleries and activities',
                    'Records are retired and restored rather than deleted',
                ],
                'engineering' => [
                    '439 automated tests',
                    'Staff passwords checked against known data breaches',
                    'CSV export protected against spreadsheet formula injection',
                ],
                'tech' => ['Laravel', 'PHP', 'Vue 3', 'Inertia.js', 'Tailwind CSS', 'Vite', 'PostgreSQL', 'SQLite', 'iCalendar (ICS) feeds', 'Resend email', 'PHPUnit', 'Docker', 'Coolify'],
            ],
            [
                'slug' => 'gym-nutrition-app',
                'title' => 'Gym nutrition app',
                'tagline' => 'Macro tracking and AI meal plans for gym members',
                'category' => 'ai',
                'status' => 'demo',
                'client_label' => 'Pitch demo for a gym',
                'role' => 'Design, build',
                'year' => 2026,
                'summary' => 'An installable app where members set macro targets, log meals by ounces or hand-sized portions, track weight and water, and get AI meal plans built around their targets, capped at three a day. Coaches get a read-only view of every member.',
                'highlights' => [
                    'Daily macro dashboard with a seven-day protein average',
                    'Meal logging by weight or by hand portions',
                    'AI meal plans written by Claude on the server, limited per member and per day',
                    'Coach view of all members',
                    'Built-in security checks screen that demonstrates the data rules live',
                ],
                'engineering' => [
                    'PostgreSQL row-level security: members can only ever read their own rows',
                    'Members cannot promote themselves to admin; the role column is not writable from the app',
                    'The AI key lives only in a server-side function and never reaches the browser',
                ],
                'tech' => ['JavaScript', 'Vite', 'Progressive Web Apps', 'Supabase', 'PostgreSQL', 'Row-level security', 'Supabase Edge Functions', 'TypeScript', 'Claude API (Anthropic)', 'Vitest', 'Vercel', 'Docker', 'Nginx'],
            ],
            [
                'slug' => 'statement-ledger',
                'title' => 'Bank-statement bookkeeping tool',
                'tagline' => 'Turns bank and card PDFs into a reconciled, tax-ready ledger',
                'category' => 'tool',
                'status' => 'internal',
                'client_label' => 'Medical practice, Haiti',
                'role' => 'Design and build',
                'year' => 2026,
                'summary' => 'A local tool that reads bank and credit-card statement PDFs in US dollars and gourdes, proves every statement to the cent against its printed totals, and sorts transactions into tax categories for review.',
                'highlights' => [
                    'Statement parsing that reconciles each statement against its own totals',
                    'Rules engine that separates business, personal and transfer transactions',
                    'Browser-based review screen with bulk reclassification',
                    'Accountant-ready Excel workbook export',
                ],
                'engineering' => [
                    'First real run: 28 statements and 1,095 transactions, all balanced',
                    'About 266 automated checks with no third-party test framework',
                    'Locks fiscal years that have already been filed',
                ],
                'tech' => ['Python', 'SQLite', 'HTML & CSS', 'JavaScript'],
            ],
        ];
    }
}
