# Duro Portfolio → Laravel + Filament Rebuild Spec

> **Decisions locked in (round 2):** new app lives in `C:\Users\ucgni\Herd\durolord` on **Laravel + Filament v5**; hosting is **cPanel on durolord.com**, with possible **subdomains per project**; "Transactions & Payments System" is the project's final name (screenshots/links added later); the CV is sourced from `public/Oluwadurotiwa Akinbo CV Styled.docx`; `/components` stays public but is moved out of the main nav (see §8); the **HRMS is cleared to be public** and launches first as a demo at **hrms.durolord.com** (source: `C:\Users\ucgni\Herd\hrms`, see §12c); other project subdomains come later.

Written for: you (or a coding agent) rebuilding the current plain-PHP portfolio as a Laravel app with a Filament admin panel and a browsable project gallery.

## 0. Goals

1. **Parity**: every page, feature and piece of content in the current app exists in the new one.
2. **Admin-managed content**: everything that is hard-coded today (guardians, certifications, CV, projects, site copy) moves into the database and is editable in Filament.
3. **Projects become a browsable catalogue**: filter, search, sort, open a full case study with a gallery, links and tech stack. Adding a project is a form in the admin, not a code change.
4. **Better graphics**: a more distinctive, polished visual layer while keeping the Duro identity (indigo/iris/gold, guardians, light/dark + palettes).

Non-goals: user accounts for visitors, comments, e-commerce, a blog (see §14 for later ideas).

---

## 1. What exists today (inventory)

The current app is a hand-rolled MVC in `app/` (custom `Router`, `View`, static-array "models") served from `public/index.php`. Alpine.js and a Tailwind build (`app/Views/partials/tailwind.php`) sit on top of ~2,000 lines of hand-written CSS.

### Routes → pages

| Route                      | Current view                                               | Notes                                                                                                                                                      |
| -------------------------- | ---------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `GET /`                    | `home.php`                                                 | Hero, "What I deliver" (3 cards), Guardians teaser (3 groups), Selected projects (4 cards), "Signals & credentials" timeline + "How I work" stats, CTA     |
| `GET /guardians`           | `guardians.php` + `guardians.js` / `guardians-carousel.js` | 3D rotating carousel ("wheel") of 15 guardians, drag/swipe + arrows, lazy background images                                                                |
| `GET /projects`            | `projects.php`                                             | 4 long-form case studies on one page with anchor nav (`#hrms`, `#service-tracker`, `#bible-tracker`, `#forex-manager`)                                     |
| `GET /certifications`      | `certifications.php`                                       | Featured links (Credly, 2× freeCodeCamp), 3 grouped lists (IBM web, IBM AI/data, Acronis cloud ×14, with Active/Expired badges), certificate image gallery |
| `GET /components`          | `components.php` + `components.js`                         | "65 reusable components" showcase (nav shells, forms, alerts, mockups, code windows)                                                                       |
| `GET /cv`                  | `cv.php` + `cv.js`                                         | CV page, "Download PDF" via browser print                                                                                                                  |
| `GET/POST /contact`        | `contact.php`, `ContactController`                         | Long brief form, honeypot, client + server validation, toast, SMTP email                                                                                   |
| 404 / 500                  | `errors/*.php`                                             | Themed error pages                                                                                                                                         |
| `/tests/contact-form-send` | `TestController`                                           | Dev-only mail test (do **not** port)                                                                                                                       |

### Cross-cutting features

- Light/dark mode + **14 accent palettes** (7 light, 7 dark: sapphire, light-blue, sunrise, evergreen, citrus, mist, blush / nebula, dark-blue, midnight, ember, onyx, noir, tundra), persisted in `localStorage` (`duro-theme-palette-{mode}`). Defined in `public/assets/js/app.js`.
- Design tokens in `public/assets/css/base.css` (`--duro-indigo #5e3bff`, `--duro-iris #8b5cf6`, `--duro-gold #f5d547`, `--duro-ink #0b1020`, radii 10/16/22px, container 1180px, Space Grotesk + Inter).
- Sticky header, mobile nav with overlay, logo swaps per colour scheme, tooltips.
- Section-head partial reused across pages.
- Favicon swaps for dark scheme.

### Content to migrate (source of truth: `app/Models/Portfolio.php`, `app/Controllers/PageController.php`, `app/Views/projects.php`)

- 15 guardians (name, tag, symbol_key, image, role, symbol, essence, aura, power, optional note) + 15 `.webp` images in `public/assets/images/`.
- 3 certification groups, 19 certifications (name, issuer, issued/expired text, status, tags), 3 featured links, 1 certificate image.
- CV: name, title, headline, summary, location, availability, contact email, focus pills, 4 skill groups, 2 CV projects, featured certs.
- 4 projects with full case-study text (problem / solution / focus / outcome / snapshot / stack / highlights).
- Logos: `Light Logo.png`, `Dark Logo.png`, `favicon.ico`.

### Known problems to fix in the rebuild

- **`ContactController.php` hard-codes the SMTP host, username and password and the destination email, and that file is committed to git.** Treat that password as leaked: **rotate it now**, and move all mail config to `.env`. Do not copy it into the new repo.
- `.env`, `app.zip`, `public.zip`, `node_modules/`, `captures/` are tracked or untracked clutter. Don't carry them over.
- PHPMailer is vendored under `public/assets/phpmailer` (publicly reachable). Replaced by Laravel Mail.
- `three-axis.js` is an empty file: a planned Three.js "axis" hero that was never built (see §9.3).
- Case-study copy says "Laravel (or custom stack)" in places. The CV docx gives the real stacks (see §12); use those.
- Project IDs/anchors are inconsistent (`forex-manager` vs "Transactions & Payments" on the home page). Resolved: the project is **Transactions & Payments System** (slug `transactions-payments`); rewrite the case-study copy from the Forex wording when you add its content.

---

## 2. Target stack

| Concern        | Choice                                                                                                                          |
| -------------- | ------------------------------------------------------------------------------------------------------------------------------- |
| Framework      | Laravel (latest stable), PHP 8.3+                                                                                               |
| Admin          | **Filament v5** (pin `^5.0`; verify the Spatie media-library plugin and any other Filament plugin supports v5 before adding it) |
| Frontend       | Blade + Livewire + Alpine.js (Alpine ships with Livewire/Filament)                                                              |
| CSS            | Tailwind CSS v4 via Vite, design tokens as CSS variables                                                                        |
| Uploads/images | `spatie/laravel-medialibrary` + `filament/spatie-laravel-media-library-plugin` (WebP/AVIF conversions, responsive images)       |
| Ordering       | `spatie/eloquent-sortable` (or Filament `reorderable()`)                                                                        |
| Slugs          | `spatie/laravel-sluggable`                                                                                                      |
| SEO            | `spatie/laravel-sitemap`, per-page meta + Open Graph                                                                            |
| Spam           | `spatie/laravel-honeypot` + route throttle                                                                                      |
| Rich text      | Filament `RichEditor` for case-study body (or Markdown editor)                                                                  |
| 3D / motion    | Three.js (hero), GSAP + ScrollTrigger (optional), CSS view transitions                                                          |
| DB             | SQLite for dev, MySQL in prod (current stack is MySQL)                                                                          |
| Tests          | Pest                                                                                                                            |
| Local dev      | Laravel Herd (already in use: `C:\Users\ucgni\Herd\`)                                                                           |

Create the new app in **`C:\Users\ucgni\Herd\durolord`** (Herd serves it at `durolord.test`), not inside the old repo, then copy assets across. Keep the old repo untouched as the reference until launch.

```bash
cd C:\Users\ucgni\Herd
composer create-project laravel/laravel durolord
cd durolord
composer require filament/filament:"^5.0"   # check the v5 install docs; Livewire comes with it
php artisan filament:install --panels      # panel id: admin, path: /admin
composer require spatie/laravel-medialibrary filament/spatie-laravel-media-library-plugin
composer require spatie/laravel-sluggable spatie/eloquent-sortable spatie/laravel-honeypot spatie/laravel-sitemap
composer require --dev pestphp/pest pestphp/pest-plugin-laravel
npm i -D three gsap
php artisan make:filament-user
```

---

## 3. Information architecture & routes

```
/                       Home
/projects               Project browser (filter / search / sort)
/projects/{slug}        Case study
/guardians              Guardian wheel
/certifications         Certifications
/cv                     CV (print-optimised)
/components             UI component showcase (keep, see §8)
/contact                Contact form (POST /contact)
/sitemap.xml            Generated
/admin                  Filament panel (auth required)
```

Keep legacy URL redirects (`/projects.php`, `/guardians.php`, `/cv.php`, …) and old anchors (`/projects#hrms` → `/projects/hr-management-system`) as 301s in `routes/web.php`, so existing links survive.

---

## 4. Data model

All content tables get timestamps. "Sortable" = has an `sort_order` int column.

### 4.1 Projects (the core new feature)

**`projects`**
| Column | Type | Notes |
|---|---|---|
| id | bigint | |
| title | string | "HR Management System" |
| slug | string unique | auto from title |
| tagline | string | "Centralizing people, roles, and internal workflows into one web system." |
| summary | text | short card blurb |
| problem | longText (rich) | "The Problem" |
| solution | longText (rich) | "The Solution" |
| solution_points | json | array of bullet strings |
| focus | longText (rich) | "What I Focused On" |
| focus_points | json | bullets |
| outcome | longText (rich) | "Outcome" |
| type | string | free text, e.g. "HR Management System (Internal tool)" |
| role | string | "Full-stack developer" |
| scope | string | "Design, backend, frontend" |
| highlights | json | sidebar highlights |
| platform | enum | `web`, `mobile`, `api`, `desktop`, `other` |
| status | enum | `live`, `in_progress`, `archived`, `private` (internal/NDA) |
| client | string nullable | |
| year | smallint nullable | |
| started_at / completed_at | date nullable | |
| live_url | string nullable | |
| repo_url | string nullable | |
| demo_video_url | string nullable | YouTube/Vimeo/Loom |
| is_featured | bool | shows on Home |
| is_published | bool | draft support |
| sort_order | int | |
| meta_title / meta_description | string nullable | SEO overrides |

Media collections (Spatie): `cover` (single), `gallery` (many, with captions via custom properties), `thumbnail` conversions.

**`technologies`**: `id, name, slug, icon (svg/iconify name), color nullable, sort_order`
**`project_technology`** pivot (many-to-many). Technologies are shared (Laravel, MySQL, Flutter, Dart, Alpine, Livewire…) and double as the browse filter.
**`project_categories`** (optional, `id, name, slug`) + pivot, e.g. "Internal tools", "Mobile", "Finance", "Productivity". Use categories **or** platform+technologies for filtering; recommended: keep both, UI shows platform tabs + tech chips.
**`project_links`** (optional): `project_id, label, url, icon` for extra links (store listing, docs, Figma).

### 4.2 Guardians

**`guardians`**: `name, tag, symbol_key, role, symbol, essence, aura, power, note nullable, sort_order, is_published`. Media: `image` (single). Add an optional `group` enum (`alignment`, `empathy`, `curiosity`) to drive the Home page's 3 teaser cards (currently hand-written: Axis Keeper/Path-Walker/Unbroken Line; Mirror-Bearer/Open Hand/Heart-Lifter; Tome-Caller/Laughing Lantern/Circuit-Sage).
Split the compound `name` ("Honesty at the Center - The Axis Keeper") into `virtue` + `title` columns for better layouts; keep a computed full name.

### 4.3 Certifications

**`certification_groups`**: `title, description, sort_order`
**`certifications`**: `group_id, name, issuer, issued_at date nullable, expires_at date nullable, status (active|expired|computed), tags json, credential_url nullable, is_featured bool, featured_cta, featured_meta, sort_order`. Compute status from `expires_at` instead of storing "Expired Mar 14, 2024" strings.
**`certificate_images`**: just a media collection `certificates` on a `settings`/`profile` model, with captions.

### 4.4 Profile / CV / site settings

_CV source of truth: `public/Oluwadurotiwa Akinbo CV Styled.docx` (see §12 for the exact content to seed)._
Single-row **`profile`** (or `spatie/laravel-settings`): `name, title, headline, summary, location, availability, contact_email, focus (json), credly_url, social links (json), logo_light, logo_dark, favicon, resume_pdf (media)`.
**`skill_groups`** (`title, items json, sort_order`) → 4 groups from the CV.
CV "projects" section **reuses `projects`** (flag `show_on_cv` bool) so there is a single source of truth. CV certifications = certifications where `is_featured`.
**`experiences`**: `company, role, location, start_date, end_date nullable (null = present), summary, bullets json, sort_order`.
**`education`**: `institution, qualification, location, start_year, end_year, sort_order`.
Both are populated from the docx (two roles, two education entries). `skill_groups` maps to the docx's five skill headings (Languages, Frameworks, Tools & Platforms, AI-Assisted Development, Data & Analytics).
**Privacy:** the docx contains a phone number. Store it in `profile.phone` with a `show_phone_publicly` flag (you chose to show it, so seed it **on**; the flag lets you hide it quickly if you get spam calls).

### 4.5 Home page content

**`home_cards`** or Filament "Blocks" on a `pages` model: the "What I deliver" cards (3) and the "How I work" stats (01/02/03). Simplest: one `site_blocks` table (`key, title, body, meta json, sort_order`) grouped by `key` (`deliver`, `how_i_work`, `credential_timeline`).

### 4.6 Contact

**`contact_messages`**: `name, email, company, project_type, budget, timeline, source, components_focus, product_url, message, ip, user_agent, status (new|read|replied|spam), read_at, notes`. Every submission is stored **and** emailed (so nothing is lost if mail fails).
Dropdown option lists (project type, budget, timeline, source) currently live in `contact.php`; move them to a config file `config/portfolio.php` or a Filament-managed table.

### 4.7 Analytics (optional, cheap)

**`project_views`** (`project_id, date, count`) increment on case-study view; shows a "Most viewed" widget in the admin and a "Popular" sort option. Skip until later if it adds friction.

---

## 5. Filament admin panel

Panel `admin` at `/admin`, brand = Duro logos (light + dark), primary colour = Duro indigo `#5e3bff`, dark mode on, custom favicon. Restrict to your user only (`canAccessPanel`), disable registration, enable 2FA (Filament MFA) and `->databaseNotifications()` for new contact messages.

### Resources

| Resource                                   | Highlights                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |
| ------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **ProjectResource**                        | Tabs form: _Overview_ (title, slug, tagline, summary, platform, status, year, flags), _Story_ (problem/solution/focus/outcome rich editors, repeaters for bullet lists), _Media_ (cover + gallery with reorder, captions, video URL), _Details_ (type/role/scope/highlights, technologies multi-select with create-inline, links repeater), _SEO_. Table: cover thumbnail, title, platform badge, status badge, technologies, featured toggle column, `reorderable('sort_order')`, filters (platform, status, tech, featured), bulk publish/unpublish, "View on site" action. |
| **TechnologyResource**                     | Name, icon, colour, usage count column.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
| **GuardianResource**                       | Image upload with editor/crop, all text fields, group select, reorderable grid.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               |
| **CertificationGroupResource**             | With a `CertificationsRelationManager` (inline add/reorder).                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |
| **CertificationResource**                  | Quick edit of status/expiry, featured toggle, filters by issuer/status.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
| **SkillGroupResource**                     | Title + `TagsInput`/repeater for items.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
| **ContactMessageResource**                 | Read-mostly: inbox table with unread badge in nav (`getNavigationBadge`), infolist view, status actions (mark read/replied/spam), reply-by-email action (`mailto:`), export, delete. No create.                                                                                                                                                                                                                                                                                                                                                                               |
| **SiteBlockResource** or **Settings page** | Home copy, deliver cards, how-I-work stats.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
| **Profile settings page** (custom `Page`)  | Name, headline, summary, availability, location, email, socials, logos, resume PDF.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           |

### Dashboard widgets

- Stats: published projects, unread messages, messages this month, expiring-soon certifications.
- Latest contact messages table.
- (Optional) Project views chart.

### Policies/validation

- Slug uniqueness, URL fields validated as URLs, image mime/size limits, at most one `cover`.
- `Project` boot hook: when `is_published` false it never appears in public queries (global scope `Published`).

---

## 6. Public pages (Blade + Livewire)

Layout: `resources/views/layouts/app.blade.php` with `<x-nav>`, `<x-footer>`, `<x-section-head>`, `<x-card>`, `<x-badge>`, `<x-btn>`, plus `@stack('head')` for per-page SEO. Components live in `resources/views/components/`.

### 6.1 Home

Same section order as today, now data-driven:

1. **Hero**: eyebrow, H1 ("I build digital realms where code, story, and identity meet."), lead, CTAs (_Start a project_, _Browse projects_, _Meet the Guardians_), focus pills, featured-guardian card (rotating through guardians, or pinned).
2. **What I deliver**: 3 cards from `site_blocks`.
3. **Guardians teaser**: 3 grouped cards with guardian thumbnails (new: show the actual art, not just text).
4. **Selected projects**: featured projects (`is_featured`, ordered), rich cards with cover image, platform badge, tech chips, "View case study".
5. **Signals & credentials**: timeline + "How I work" stats.
6. **CTA** to contact.

### 6.2 Project browser: `/projects` (new)

Livewire component `ProjectBrowser`:

- **Search** (title, tagline, summary, technology names), debounced.
- **Filters**: platform tabs (All / Web / Mobile / …), technology chips (multi-select, with counts), status, year.
- **Sort**: Featured first (default), Newest, Oldest, A-Z, (Most viewed).
- **View toggle**: grid (image cards) / list (compact).
- State in the **query string** (`#[Url]`) so filtered views are shareable; browser back works.
- Pagination or "load more" (infinite scroll after 12), skeleton loaders, empty state ("No projects match, clear filters").
- Cards: cover image (WebP, lazy, aspect 16:10), title, tagline, platform + status badge, top 3 tech chips, hover tilt/spotlight, link to case study. `wire:navigate` for SPA-feeling transitions.
- A short intro paragraph + count ("12 projects") from the existing page's lede.

### 6.3 Case study: `/projects/{slug}`

Preserves today's structure (it works well), upgraded:

- **Header**: title, tagline, badges (platform/status/year), action buttons (_Live site_, _Source_, _Watch demo_) shown only when present.
- **Hero media**: cover image or video embed.
- **Main column**: The Problem → The Solution (+ bullets) → What I Focused On (+ bullets) → Outcome (highlighted block), rendered from the rich-text fields; sections hidden if empty.
- **Sidebar (sticky)**: Project Snapshot (type/role/scope/client/year), Tech Stack (chips linking to `/projects?tech=slug`), Highlights, extra links.
- **Gallery**: masonry/grid of screenshots with a **lightbox** (Alpine, keyboard + swipe, captions, zoom).
- **Table of contents** (scrollspy) on desktop.
- **Next / previous project** footer, plus "Related projects" (shared technologies).
- **CTA**: "Start a project conversation".
- JSON-LD (`CreativeWork`), OG image from cover.
- Private/NDA projects (`status=private`): show the story and hide links/screenshots.

### 6.4 Guardians

Port the 3D wheel (`guardians.js`) to an Alpine component; data from DB. Improvements: keyboard arrows, accessible `aria-live` announcement of the active guardian, `prefers-reduced-motion` fallback to a simple scroll-snap row, `loading="lazy"` real `<img>` tags (with responsive srcset from Media Library) instead of JS-injected background images. Add a **grid view** toggle (all 15 at once) and deep links `/guardians#axis-keeper`.

### 6.5 Certifications

Featured strip, grouped lists with Active/Expired badges (computed), tag chips, certificate image gallery with lightbox. Add filter by issuer/status (Alpine, no server round trip).

### 6.6 CV

Data-driven from profile + skills + projects (`show_on_cv`) + featured certs. Downloads: (1) **DOCX**, the styled CV, uploaded in the profile settings page (`resume_docx` media collection) and served at `/cv/download/docx`; (2) **PDF**, an uploaded `resume_pdf` (export the docx to PDF once) served at `/cv/download/pdf`. Don't generate PDFs server-side on cPanel (Dompdf fidelity and memory limits are a hassle); upload both files and replace them when the CV changes. Keep a browser "Print" button with a `@media print` stylesheet as a fallback. The CV page itself renders from the DB (experience, education, skills, projects, certs), so it is the web version of the docx and stays in sync with the rest of the site.

### 6.7 Contact

Same fields as the current form. Implementation:

- `ContactRequest` FormRequest (name, email required; `message` min length; `product_url` nullable URL; select values validated against the config option lists).
- Livewire or plain form with Alpine inline validation (current UX) and a toast; server validation is the source of truth.
- Honeypot (`spatie/laravel-honeypot`) + `throttle:5,10`.
- On success: create `ContactMessage`, queue `ContactMessageReceived` Mailable to `config('portfolio.contact_to')`, fire Filament database notification, optional auto-reply to sender.
- Mail via `.env` (`MAIL_*`); queue with `database` driver so a mail outage never loses the message.
- Accept a `?project=slug` param so "Discuss this project" buttons on case studies pre-fill the form.

### 6.8 Errors

Themed `404.blade.php`, `500.blade.php`, `503.blade.php` (maintenance) with a guardian illustration.

### 6.9 SEO / meta

Per-page `<title>`, description, canonical, OG/Twitter cards, `sitemap.xml` (static pages + published projects), `robots.txt`, favicon set (light/dark `<link media>` as today).

---

## 7. Theming: keep the Duro identity, make it systematic

- Define tokens once in `resources/css/app.css` with Tailwind v4 `@theme` (colours, radii, fonts, shadows). Map today's `--bg`, `--surface`, `--accent`, `--accent-2`, `--border`, etc. 1:1 so palette presets keep working.
- Light/dark via `data-theme` on `<html>` (set in an inline `<script>` in `<head>` **before paint** to avoid flash), default to `prefers-color-scheme`.
- Port the 14 palette presets from `public/assets/js/app.js` into a single `resources/js/palettes.js`; keep the cycle button and per-mode persistence. Optional: let the admin pick the _default_ palette.
- Fonts: Space Grotesk (headings) + Inter (body), self-hosted via `@fontsource` (drops the Google Fonts request and layout shift).
- Filament panel theme: `php artisan make:filament-theme`, reuse the same tokens so admin feels like the same product.

---

## 8. The `/components` showcase

**Recommendation: keep it public, but demote it.** It is real proof of UI craft and costs nothing to host, but it isn't what a client or recruiter comes to the site for. So: remove it from the main nav, link it from the footer ("UI kit") and from the skills/CV area. An admin setting `show_components_in_nav` (default off) lets you promote it later.

Port the current page as-is first. After launch, rebuild it **on the real Blade components** the site uses, so it stops being a parallel copy of the CSS and doubles as living documentation (Navigation, Forms, Feedback, Data display, Mockups).

---

## 9. Improved graphics (the visual upgrade)

Principle: **richer, not noisier**. Everything degrades under `prefers-reduced-motion` and on low-power devices.

### 9.1 Visual language

- **Aurora/mesh gradient** backgrounds (indigo → iris → gold glow) on hero and section dividers, animated very slowly; static in reduced-motion.
- **Glassmorphism** surfaces for header, cards and the guardian overlay (backdrop-blur, 1px gradient borders, inner highlights).
- **Grain/noise overlay** (tiny tiled PNG/SVG at ~4% opacity) to remove banding and add depth.
- Consistent **elevation scale** and **glow** tokens in the accent colour; gradient text for key words in H1.
- Section dividers: SVG waves/rune lines tied to the guardian motifs.
- Replace ad-hoc inline SVG icons with one set (Heroicons via `blade-heroicons`, or Phosphor/Lucide) and a technology icon set (`blade-simple-icons`/`devicon`) for stack chips.

### 9.2 Imagery

- All art through Media Library: generate **AVIF + WebP** conversions and `srcset`, with blurhash/LQIP placeholders and explicit width/height to kill CLS.
- Project covers: consistent 16:10 crops; admin crop tool (Filament image editor). Optional **device mockup frames** (browser window / phone) composed in CSS around screenshots so every project looks uniform.
- Auto-generated **OG images** per project (title + cover over the brand gradient) using `spatie/browsershot` or a simple GD/Intervention template, cached.

### 9.3 Hero: the unbuilt "axis" (`three-axis.js` is empty today)

A Three.js scene: a vertical beam of white-gold light (the Axis Keeper's symbol) with drifting particles and subtle mouse parallax. Lazy-initialised via `IntersectionObserver`, capped at 60fps/devicePixelRatio 2, **disabled** (replaced by a static gradient+PNG) for reduced-motion, `saveData`, or no WebGL. Keep it under ~150 KB gzipped (import only what's needed).

### 9.4 Motion

- Page transitions: Livewire `wire:navigate` + CSS View Transitions API (shared-element transition from project card cover → case-study hero).
- Scroll reveals (IntersectionObserver or GSAP ScrollTrigger), staggered card entrances, animated stat counters.
- Card hover: tilt + moving spotlight following the cursor (CSS custom properties updated in a tiny Alpine directive).
- Guardian wheel: keep the 3D carousel, add per-guardian accent colour (derived from `symbol_key`) that tints the background and glows around the active card; slow parallax of the image inside the card.
- Nav: animated active-pill indicator sliding between links.
- Micro-interactions: button press, toast spring, form field focus ring in accent.

### 9.5 Per-guardian colour map

Use `symbol_key` to drive accents, e.g. axis = white-gold, mirror = silver, path = amber, iron = steel grey, crown = muted gold, line = cyan, flame = orange, hand = warm gold, stone = rune green, rhythm = blue, lantern = gold sparks, tome = leather brown/amber, circuit = electric blue, heart = soft gold, knot = violet. Store as a `accent_color` column (editable in admin) with these as seeds.

---

## 10. Performance, accessibility, security

**Performance**: Vite build, no CDN Tailwind runtime, self-hosted fonts, `loading="lazy"` + `fetchpriority="high"` on the LCP image, `route:cache`/`config:cache`/`view:cache` in deploy, cache the Home and Projects queries (`Cache::rememberForever` busted by model events), Lighthouse budget ≥ 90 on all four categories.

**Accessibility**: WCAG 2.2 AA contrast in _every_ palette (add a script/test that checks `--text` vs `--bg` and `--accent` vs `--surface` for all 14 presets), visible focus states, skip-link, proper landmarks, keyboard-operable carousel/lightbox/tabs, `aria-current` on nav, form errors tied to inputs via `aria-describedby`, reduced-motion support.

**Security**: secrets only in `.env`; throttle contact + login; honeypot; CSRF (default); escape rich text with a sanitiser (`mews/purifier` or Filament's built-in) before rendering; Filament panel locked to your account; set security headers (CSP report-only first, X-Frame-Options, Referrer-Policy) via middleware; `APP_DEBUG=false` in prod.

---

## 11. Project structure (target)

```
app/
  Enums/            ProjectPlatform, ProjectStatus, MessageStatus, CertStatus
  Filament/
    Resources/      Project, Technology, Guardian, CertificationGroup, Certification,
                    SkillGroup, ContactMessage, SiteBlock
    Pages/          ProfileSettings
    Widgets/        StatsOverview, LatestMessages
  Http/
    Controllers/    HomeController, ProjectController(show), GuardianController,
                    CertificationController, CvController, ContactController
    Requests/       ContactRequest
  Livewire/         ProjectBrowser, ContactForm (optional)
  Mail/             ContactMessageReceived, ContactAutoReply
  Models/           Project, Technology, Guardian, CertificationGroup, Certification,
                    SkillGroup, ContactMessage, SiteBlock, Profile
config/portfolio.php   contact_to, form option lists, feature flags
database/
  migrations/ factories/
  seeders/          DatabaseSeeder, GuardianSeeder, CertificationSeeder, ProjectSeeder,
                    ProfileSeeder, SiteBlockSeeder  (see §12)
resources/
  css/app.css       tokens, base, components
  js/               app.js, palettes.js, guardian-wheel.js, hero-axis.js, lightbox.js
  views/            layouts/, components/, pages/, projects/, errors/
routes/web.php
tests/Feature/      ContactTest, ProjectBrowserTest, ProjectShowTest, AdminAccessTest
```

---

## 12. Migrating the existing content (seeders)

1. Copy `public/assets/images/Guardian *.webp`, logos, favicon and `public/assets/certs/*` into `database/seeders/assets/` (not `public/`), and have the seeders attach them through Media Library (`->addMedia(...)->preservingOriginal()->toMediaCollection()`).
2. **GuardianSeeder**: port the array from `app/Models/Portfolio.php::guardians()` in order; assign `group` for the 9 teased guardians.
3. **CertificationSeeder**: port `certificationGroups()` and `featuredCertifications()`. Convert "Issued Aug 26, 2020" / "Expired May 17, 2025" into real dates (`issued_at` / `expires_at`); IBM entries have no expiry.
4. **ProfileSeeder / SkillGroupSeeder**: port the `$cv` array from `PageController::cv()`.
5. **ProjectSeeder**: four projects from `app/Views/projects.php` and the Home cards. Normalise as follows:

| Slug                    | Title                          | Platform | Stack (from the CV)             | Status                                                                                              | Home badge |
| ----------------------- | ------------------------------ | -------- | ------------------------------- | --------------------------------------------------------------------------------------------------- | ---------- |
| `hr-management-system`  | HR Management System           | web      | Laravel, Filament, MySQL        | `live` (cleared for public; demo at `https://hrms.durolord.com`, fake data; set `demo_credentials`) | Laravel    |
| `service-tracker`       | Service Tracker                | web      | PHP, MySQL, HTML/CSS/JS         | set when links exist                                                                                | Tracker    |
| `transactions-payments` | Transactions & Payments System | web      | Laravel, MySQL, Blade, Livewire | screenshots and links **added later**; keep `is_published=false` until then, or publish story-only  | Laravel    |
| `bible-reading-tracker` | Bible Reading Progress App     | mobile   | Flutter, Dart                   | set when links exist                                                                                | Flutter    |

Problem/solution/focus/outcome/bullets/snapshot/highlights text is already written in `projects.php`; copy it verbatim. The Home page's short card text and tag chips ("Roles & Permissions", "Reporting-ready", …) become `summary` and a `tags` json column (or technologies/categories). The fourth project's text in `projects.php` is the Forex wording; keep it as placeholder `problem/solution/focus/outcome` but retitle it **Transactions & Payments System** and use the CV line ("auditable records with clear purchase-vs-payment tracking and exports") for the summary. 6. **CV seeding from the docx** (`ProfileSeeder`, `ExperienceSeeder`, `EducationSeeder`, `SkillGroupSeeder`):

- Title: _Full-Stack Web Developer_. Profile paragraph: copy the docx "PROFILE" text (6+ years, Laravel/PHP/JS/MySQL, Flutter/WordPress, AI-assisted development). This **replaces** the old "Creative Developer & Worldbuilder" CV summary; that wording can stay on the Home hero as brand voice.
- Contact: Lagos, Nigeria (remote-first), email, durolord.com, Credly profile; phone shown (`show_phone_publicly` on).
- Experience: **Full-Stack Developer (Remote), Edoubleone, United States, Feb 2026 – Present** (4 bullets) and **Full-Stack Web Developer, Tishri Infotech Nig Ltd, Lagos, 2018 – Jan 2026** (6 bullets: HRMS with Laravel/Filament/MySQL, front/back-end delivery, Laravel/WordPress/Docker/Git/Bootstrap/Tailwind, networking and Starlink, generative AI tools, Power BI/Python/SQL/R).
- Education: BSc Computer Science, Caleb University, Imota (2021–2025); SSCE, Kings Anchor College, Lagos (2015).
- Skills: Languages (HTML, CSS, JavaScript, PHP, Dart, SQL); Frameworks (Laravel, Filament, Livewire, WordPress, Flutter, Tailwind, Bootstrap, Alpine.js); Tools & Platforms (Git, Docker, Firebase, MySQL); AI-Assisted Development (Claude, ChatGPT, GitHub Copilot-style workflows); Data & Analytics (Power BI, Python, R, KPI reporting).
- Certifications on the CV: freeCodeCamp ×2, IBM Web Foundations, IBM AI & Data Literacy, Acronis Cloud Sales & Technical Enablement.
- Attach the docx as the `resume_docx` media file.
- The old site's skills ("PHP (Laravel-style structure)") undersell the docx; the docx wins.

7. **SiteBlockSeeder**: "What I deliver", "How I work", credential timeline copy from `home.php`.
8. Make seeders idempotent (`updateOrCreate` on slug) so they can be re-run safely; in production you edit via Filament, not seeders.
9. New projects: **no code**, added through the admin (cover, screenshots, text, tech chips).

---

## 12b. Hosting on cPanel and per-project subdomains

**Main site:** `durolord.com` serves this Laravel app. Keep the app folder outside `public_html` (e.g. `/home/USER/durolord`) and set the domain's **document root to `durolord/public`**. Never expose the app folder itself.

**Subdomains for projects (optional, per project):** worth it only when a project has something to _show running_.

- Each live project is its own deployment (`hrms.durolord.com`, `payments.durolord.com`, `tracker.durolord.com`), created in cPanel under _Domains_, each with its own docroot, database and `.env`. Put that URL in the project's `live_url` in the admin; the case-study "Live site" button uses it.
- **Don't** build subdomain routing or multi-tenancy into the portfolio. It just _links_ out. Projects without a subdomain simply show no live button.
- For private/internal systems (HRMS) **never deploy a public demo with real data**. If you want a demo, deploy a separate instance seeded with fake data, behind a "Demo" banner, with the demo login shown on the case-study page (new nullable `demo_credentials` field on projects, rendered as a callout).
- Demo subdomains: `noindex` header, and a scheduled reset (`migrate:fresh --seed`) so visitors can't leave junk.
- cPanel AutoSSL issues a certificate per subdomain, so no wildcard cert is needed. If cPanel isn't your DNS host, add `A`/`CNAME` records at the registrar.

**cPanel constraints that shape the build** (confirm with your host):

- **No long-running workers.** Use `QUEUE_CONNECTION=database` plus a per-minute cron `php /home/USER/durolord/artisan schedule:run` with `Schedule::command('queue:work --stop-when-empty --max-time=50')->everyMinute()`. Contact messages are saved to the DB first, so a mail failure never loses one.
- **Node/Vite is usually unavailable**, so run `npm run build` locally and ship `public/build` (via Git Version Control, rsync or GitHub Actions). If Composer isn't available over SSH, run `composer install --no-dev -o` locally and upload `vendor/`.
- Choose **PHP 8.3+** in MultiPHP Manager; enable `intl`, `gd` or `imagick`, `zip`, `exif`, `fileinfo`, `mbstring`, `bcmath`, `pdo_mysql`. AVIF needs `imagick`; otherwise WebP only.
- `storage:link` may be blocked: create a relative symlink in Terminal, or point the media disk at a folder under `public/`.
- Raise `upload_max_filesize`/`post_max_size` in MultiPHP INI Editor for screenshots.
- Email: create a cPanel mailbox and use its SMTP in `.env` with **new** credentials.
- Enable automatic backups (JetBackup or similar) for the DB and `storage/app/public`.
- Deploy script (`.cpanel.yml` or CI): `composer install --no-dev -o`, `php artisan migrate --force`, `php artisan optimize`, `php artisan filament:optimize`.

---

## 12c. HRMS demo launch (hrms.durolord.com)

Source: `C:\Users\ucgni\Herd\hrms`. Stack found there: Laravel 11, Filament 3, Shield (roles), Spatie permission/activitylog/medialibrary, DomPDF, FullCalendar, queueable bulk actions, SQLite locally, panel mounted at `/`. It already seeds ~40 fictional Nigerian users in six roles (`password` for all) plus employees, payroll, attendance, leave, openings and applicants, so it is a natural **public demo with fake data**. Never point it at real Tishri Infotech data.

### Done (branch `deploy-prep`, commit `0f8acdb`, not merged)

- **Security fix:** `/payroll/{id}/download-pdf` and `/download-cv/{id}` were **publicly reachable with no login**, so anyone could enumerate payslips and applicant CVs by ID. Both now require authentication and the existing policy (`PayrollPolicy::view`, `ApplicantPolicy::viewAny`). Verified: logged-out requests now 302 to the login page.
- `DEMO_MODE` flag (`config('app.demo')`): login form pre-fill (`olumide_adebayo@example.com` / `password`) only when on; demo banner (links back to the case study); `noindex` meta; `robots.txt` now `Disallow: /`.
- `php artisan demo:reset` (refuses to run unless `DEMO_MODE=true`) does `migrate:fresh --seed` and clears uploaded applicant files; scheduled nightly at 03:00. The queue is drained from the scheduler every minute (needed for cPanel and for the queueable bulk actions/exports).
- `.env.production.example` with production-safe values (`APP_DEBUG=false`, MySQL, `Africa/Lagos`, secure cookies, log mailer).

### Still to do before launch

1. **Merge** `deploy-prep` into `main` after a click-through as Admin, HR Manager, Finance Manager and Employee (check payslip download for own vs others' payroll as an Employee).
2. **Framework support:** Laravel 11 is past its security-support window and Filament 3 is two majors behind v5. For a short-lived demo it is acceptable to launch as-is after `composer update` within the current constraints and `composer audit`; plan the upgrade (Laravel 12/13 → Filament 4/5, replacing or updating Shield, flatpickr, activity-log, fullcalendar, multi-widget and queueable-bulk-actions plugins, several of which may lag) as a separate task. Don't block the portfolio launch on it, but don't leave it indefinitely.
3. ✅ **Done (uncommitted on `deploy-prep`): make demo users harmless:** all accounts share a public password, so demo-mode should (a) block changing passwords/emails and the Profile page, (b) block deleting users/roles and editing Shield roles, and (c) rate-limit login (`throttle`). Otherwise the first visitor can lock everyone out until the 03:00 reset.
4. ✅ **Done (uncommitted): uploads in demo:** the public job page (`/jobs`) accepts applications with CV files. Add file-type/size limits and a captcha or honeypot, or disable submission in demo mode, since it is an open upload endpoint.
5. ✅ **Done (uncommitted): README and `.env.example` cleanup;** original note: `public/build` is git-ignored (build locally and upload); `README.md` still says "Laravel Cloud", Redis and `Larament`, and `.env.example` has `APP_NAME=Larament`. Rewrite the README (what it is, demo URL, demo logins, stack, screenshots) since it is now a portfolio piece, and remove unused dev tooling from the production install (`--no-dev` already excludes debugbar, Sail, Pest).
6. **Screenshots:** capture 6–8 images from the seeded demo (dashboard, employees, attendance, leave approval, payroll + payslip PDF, recruitment, roles) for the case-study gallery.
7. **Optional hardening:** HTTPS-only (`SESSION_SECURE_COOKIE`, HSTS), disable Debugbar in any non-local env, `php artisan about` and `composer audit` in CI.

### cPanel deploy steps (hrms.durolord.com)

1. cPanel → Domains → create `hrms.durolord.com`, docroot `/home/USER/hrms/public` (app outside `public_html`). Enable AutoSSL.
2. Create a MySQL database + user; PHP 8.2+ with `intl`, `gd`/`imagick`, `zip`, `mbstring`, `bcmath`, `fileinfo`, `pdo_mysql`.
3. Locally: `composer install --no-dev -o` and `npm ci && npm run build`; upload the project including `vendor/` and `public/build`, **excluding** `.env`, `node_modules`, `.git`, `tests`.
4. On the server: copy `.env.production.example` to `.env`, fill `DB_*`, then `php artisan key:generate`, `php artisan migrate --force --seed`, `php artisan storage:link` (or symlink manually), `php artisan optimize`, `php artisan filament:optimize`.
5. Cron (every minute): `php /home/USER/hrms/artisan schedule:run >> /dev/null 2>&1`.
6. Verify: login, payslip PDF, a bulk action (queue), the nightly reset (run `php artisan demo:reset` manually once), and that `https://hrms.durolord.com/payroll/1/download-pdf` is rejected when logged out.
7. In the portfolio admin: set the HRMS project's `live_url`, `demo_credentials`, and gallery screenshots, then publish.

---

## 13. Build order (milestones)

1. **Scaffold**: Laravel, Filament, Vite/Tailwind, Pest, Herd site, `.env`, git init (clean repo, `.gitignore` incl. `.env`, `node_modules`, `*.zip`).
2. **Design system**: tokens, fonts, theme/palette JS, layout, nav, footer, buttons/cards/badges as Blade components. Static Home skeleton.
3. **Data layer**: migrations, models, enums, factories, seeders with migrated content.
4. **Filament**: Profile, Technology, Project (+ media), Guardian, Certification resources; verify you can create a project with screenshots end-to-end.
5. **Projects**: browser (Livewire filters/search/sort) → case-study page → gallery lightbox → Home featured section.
6. **Remaining pages**: Guardians wheel, Certifications, CV (+PDF), Components showcase.
7. **Contact**: form, validation, DB store, queued mail, Filament inbox + notifications.
8. **Graphics pass**: aurora/glass/grain, Three.js hero axis, view transitions, scroll reveals, OG images.
9. **Hardening**: SEO/sitemap, redirects from old URLs, a11y audit (all palettes), Lighthouse, security headers, tests green.
10. **Deploy** to cPanel (see §12b); optionally rebuild `/components` on the real Blade components afterwards.

### Deploy checklist

- PHP 8.3+, `composer install --no-dev -o`, `npm run build` (locally if the host has no Node), `php artisan migrate --force`, `storage:link`, `optimize`, cron-driven queue (§12b), `filament:optimize`.
- `.env`: `APP_URL=https://durolord.com`, `APP_DEBUG=false`, new `MAIL_*` credentials (rotated), `QUEUE_CONNECTION=database`.
- DB + `storage/app/public` backups. Point DNS only after redirects are verified.

---

## 14. Acceptance criteria

- [ ] Every route in §3 works; every legacy URL 301s correctly.
- [ ] All 15 guardians, 19 certifications, CV content and 4 projects appear with the same text as today.
- [ ] I can add a new project (title, cover, gallery, tech, links, story) in `/admin` and it appears on `/projects` and its own page without touching code.
- [ ] `/projects` search, platform filter, tech filter and sort work, are reflected in the URL, and survive refresh/back.
- [ ] Unpublished projects are invisible publicly and in the sitemap.
- [ ] Contact submissions are stored, emailed, visible in the admin inbox with an unread badge, and rate-limited; the honeypot blocks bots.
- [ ] No credentials in git; `.env.example` documents every variable.
- [ ] Light/dark and all 14 palettes work, persist, and pass contrast checks.
- [ ] Reduced-motion users get no continuous animation and no WebGL.
- [ ] Lighthouse ≥ 90 (Perf/A11y/Best Practices/SEO) on Home, `/projects`, a case study.
- [ ] Pest suite passes (contact validation + mail + storage, project browser filters, published scope, admin auth gate).

## 15. Later ideas (out of scope for v1)

Blog/notes (Filament + Markdown), testimonials, project "timeline" view, per-project analytics, a public guardian-quiz ("which guardian are you"), multi-language, newsletter signup, case-study PDF export.

## 16. Open questions

Resolved: location and stack (Laravel + Filament v5 in `C:\Users\ucgni\Herd\durolord`), Transactions & Payments (name fixed, assets later), `/components` (public, out of main nav), CV (from the docx), hosting (cPanel), HRMS (public as a demo at `hrms.durolord.com`, other subdomains later), Duro Codex voice kept on Home, phone number shown publicly (set `show_phone_publicly` on).

Still open:

1. SSH/Terminal and imagick availability on the cPanel host (decides the deploy method and AVIF vs WebP-only). Ask the host or check cPanel's _Terminal_ and _Select PHP Version → Extensions_.
2. Whether to upgrade the HRMS framework stack before or after launch (§12c item 2). Recommendation: after.

Claude Code build and UI-polish instructions live separately in `CLAUDE_CODE_HANDOFF.md`.
