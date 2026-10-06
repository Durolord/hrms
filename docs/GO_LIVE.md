# HRMS demo go-live checklist (hrms.durolord.com)

Demo only: fake data, `DEMO_MODE=true`. Never point at real Tishri Infotech data.

## Before deploy (local)
- [ ] Dependency patching done (see "Open: advisories" below) and `composer audit --no-dev` re-run
- [ ] `composer install --no-dev -o`
- [ ] `npm ci && npm run build` (ship `public/build`)
- [ ] Click-through as Admin, HR Manager, Finance Manager, Employee (Employee: own payslip downloads, others' are denied)
- [ ] Logged out: `/payroll/1/download-pdf` and `/download-cv/1` redirect to login
- [ ] `php artisan demo:reset` works with `DEMO_MODE=true` and refuses without it
- [ ] 6-8 screenshots captured (dashboard, employees, attendance, leave approval, payroll + payslip, recruitment, roles)
- [ ] Merge `deploy-prep` into `main`

## Server (cPanel)
1. Create `hrms.durolord.com`, docroot `/home/USER/hrms/public`; enable AutoSSL.
2. Create MySQL DB + user; PHP 8.2+ with intl, gd/imagick, zip, mbstring, bcmath, fileinfo, pdo_mysql.
3. Upload project incl. `vendor/` and `public/build`; exclude `.env`, `node_modules`, `.git`, `tests`.
4. `cp .env.production.example .env`, fill `DB_*`, `DEMO_MODE=true`, then:
   `php artisan key:generate && php artisan migrate --force --seed && php artisan storage:link && php artisan optimize && php artisan filament:optimize`
5. Cron every minute: `php /home/USER/hrms/artisan schedule:run >> /dev/null 2>&1`
6. Verify: login, payslip PDF, a bulk action (queue), run `php artisan demo:reset` once, logged-out payslip URL rejected, `noindex` present.
7. Portfolio admin: set HRMS `live_url`, `demo_credentials`, gallery; publish.

## Open: advisories (`composer audit --no-dev`, 2026-10-06)
Laravel 11.x is fully covered by advisories, so `composer update` is blocked by Composer's
`audit.block-insecure`. Plan: set `audit.block-insecure=false`, update Filament 3.x, Guzzle,
Dompdf, Symfony within constraints, re-audit. Notable: Filament CVE-2026-55409 (high, RichEditor XSS),
CVE-2026-48500 (unauthenticated upload on auth pages), Guzzle CVE-2026-69246 (high), Dompdf SVG
file-read issues (keep payslip templates free of user-supplied SVG/remote assets).
Framework upgrade (Laravel 12/13, Filament 4/5) is a separate task; `kenepa/multi-widget` is abandoned.
