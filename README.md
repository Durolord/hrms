# HRMS: Human Resource Management System

A Laravel + Filament HR system for small and medium businesses: employees, attendance, leave, payroll with PDF payslips, recruitment and role-based access.

**Live demo:** https://hrms.durolord.com (all data is fictional and resets every hour)
**Case study:** https://durolord.com/projects/hr-management-system

## Features
- Employee records, departments, branches, designations, skills and pay scales
- Attendance check-in/out, breaks and monthly summaries
- Leave requests with approval, rejection and override flows
- Payroll with allowances, deductions, bonuses, snapshots and PDF payslips
- Recruitment: job openings, a public application page, applicant pipeline and CV downloads
- Roles and permissions (Admin, HR Manager, Finance Manager, Department Head, Employee, IT Admin) via Filament Shield
- Activity/audit log, calendar, queued bulk actions and exports

## Demo logins
In demo mode the login page lists one account per role with a one-click "sign in as" button. All of them share the password `password` (`DEMO_PASSWORD`).

| Role | Name | Email |
|---|---|---|
| Admin | Olumide Adebayo | olumide_adebayo@example.com |
| HR Manager | Adeola Afolabi | adeola_afolabi@example.com |
| Finance Manager | Bolanle Adebayo | bolanle_adebayo@example.com |
| Department Head | Abdul Rahman | abdul_rahman@example.com |
| Employee | Ugochukwu Okonkwo | ugochukwu_okonkwo@example.com |
| IT Admin | Olumide Adeyemi | olumide_adeyemi@example.com |

Once signed in, **Help & Documentation → How it works** explains the workflow, the rules and each role, with a step-by-step "Try it" walkthrough.

## Stack
Laravel 11 (PHP 8.2+), Filament 3, Livewire, Tailwind, MySQL (SQLite for local development), Spatie permission / activitylog / medialibrary, DomPDF.

## Local setup
```bash
git clone <repo-url> hrms && cd hrms
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

## Deployment (cPanel / shared hosting)
See `.env.production.example`. In short: build assets locally, upload with `vendor/`, set `.env`, run `php artisan migrate --force --seed`, `php artisan storage:link`, `php artisan optimize`, and add one cron entry:

```
* * * * * php /home/USER/hrms/artisan schedule:run >> /dev/null 2>&1
```

The scheduler drains the queue every minute and, in demo mode, runs `php artisan demo:reset` on `DEMO_RESET_CRON` (hourly by default).

## Demo mode
Set `DEMO_MODE=true` to run the app as a public demo. With it off (the default) none of the following applies and the app behaves as a normal install.

- **Login page:** pre-filled with the first demo account, a one-click "sign in as" list of every demo account (role, name, what it can do), the shared password and the time of the next reset. Password reset is hidden. One-click login only accepts the listed emails and goes through the normal Filament authentication.
- **Banner:** "Live demo. Explore freely: all data resets in N minutes" with a live countdown on every page; pages also get `noindex`.
- **Reset:** `php artisan demo:reset` runs `migrate:fresh --seed --force` (and clears uploaded CVs/avatars). It refuses to run unless demo mode is on or you pass `--force`. In demo mode the scheduler runs it on `DEMO_RESET_CRON`.
- **Protection:** demo accounts and their employee records can't be deleted; their email, password and roles can't be changed (locked on the profile page and enforced in policies and on the model). Roles are read-only. The password rehash on login and remember-token updates are allowed, so login always works.
- **Limits:** each table can gain `DEMO_MAX_NEW_ROWS_PER_TABLE` rows between resets, and each visitor IP can create records `DEMO_WRITES_PER_WINDOW` times per `DEMO_WRITE_WINDOW_MINUTES` (a bulk action counts once). When a limit is hit the action stops with a notification saying when the data resets. The panel is also throttled to `DEMO_REQUESTS_PER_MINUTE` per IP.

| Variable | Default | Purpose |
|---|---|---|
| `DEMO_MODE` | `false` | Turns demo mode on |
| `DEMO_PASSWORD` | `password` | Shared password of every demo account |
| `DEMO_RESET_CRON` | `0 * * * *` (hourly) | When `demo:reset` runs, in `APP_TIMEZONE` |
| `DEMO_WRITES_PER_WINDOW` | `30` | Record creations per IP per window (`0` = off) |
| `DEMO_WRITE_WINDOW_MINUTES` | `10` | Length of that window |
| `DEMO_MAX_NEW_ROWS_PER_TABLE` | `200` | New rows per table between resets (`0` = off) |
| `DEMO_REQUESTS_PER_MINUTE` | `90` | Panel requests per minute per IP |
| `APP_TIMEZONE` | `UTC` | Use `Africa/Lagos` for the demo |

The scheduler needs one cron entry on the server:

```
* * * * * php /path/to/hrms/artisan schedule:run >> /dev/null 2>&1
```

**Changing the demo accounts:** edit `accounts` in `config/demo.php` (role, name, email, one-line summary; the first is pre-filled). Each role must exist in `ShieldSeeder`. Then run `php artisan config:clear` and `php artisan demo:reset` (or `php artisan db:seed --class=DemoAccountSeeder`, which is idempotent). `DemoDataSeeder` builds the sample data around the Department Head and Employee accounts, so keep those two roles, or adjust the seeder. If you change emails, update the walkthrough in the README table above.

## Branding
The seal logo and favicon live in `public/images/brand` (gold on ivory for light mode, silver on black for dark mode). The favicon follows the OS theme and Filament's own theme switcher. Regenerate them with `python3 docs/brand/generate-brand.py public/images/brand`.

## License
MIT
