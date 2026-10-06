# HRMS: Human Resource Management System

A Laravel + Filament HR system for small and medium businesses: employees, attendance, leave, payroll with PDF payslips, recruitment and role-based access.

**Live demo:** https://hrms.durolord.com (all data is fictional and resets nightly)
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
Every seeded account uses the password `password`. The login form is pre-filled when `DEMO_MODE=true`.

| Role | Email |
|---|---|
| Admin | olumide_adebayo@example.com |
| HR Manager | adeola_afolabi@example.com |
| Finance Manager | bolanle_adebayo@example.com |
| Department Head | abdul_rahman@example.com |
| Employee | ugochukwu_okonkwo@example.com |

In demo mode, user and role management and email/password changes are disabled so shared accounts can't be locked out.

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

The scheduler drains the queue every minute and, in demo mode, runs `php artisan demo:reset` at 03:00 daily.

## Environment flags
| Variable | Purpose |
|---|---|
| `DEMO_MODE` | Pre-filled login, banner, `noindex`, read-only users/roles, nightly reset |
| `APP_TIMEZONE` | Defaults to UTC; use `Africa/Lagos` for the demo |

## License
MIT
