<?php

return [
    /*
    | Public demo mode: pre-filled login with one-click role accounts, a reset
    | countdown banner, noindex, protected shared accounts, write limits and
    | the scheduled demo:reset. When false the app behaves as a normal install.
    */
    'enabled' => (bool) env('DEMO_MODE', false),

    // Shared password for every demo account (and the other seeded staff in demo mode).
    'password' => env('DEMO_PASSWORD', 'password'),

    // When demo:reset runs (standard 5-field cron, app timezone). Default: hourly.
    'reset_cron' => env('DEMO_RESET_CRON', '0 * * * *'),

    // Record creations allowed per visitor IP per window; 0 disables the limit.
    'writes_per_window' => (int) env('DEMO_WRITES_PER_WINDOW', 30),
    'write_window_minutes' => (int) env('DEMO_WRITE_WINDOW_MINUTES', 10),

    // New rows any one table may gain between resets; 0 disables the cap.
    'max_new_rows_per_table' => (int) env('DEMO_MAX_NEW_ROWS_PER_TABLE', 200),

    // Requests per minute per IP on the panel in demo mode.
    'requests_per_minute' => (int) env('DEMO_REQUESTS_PER_MINUTE', 90),

    // Bookkeeping tables written as a side effect of normal use; never capped or rate limited.
    'unlimited_tables' => [
        'activity_log', 'notifications', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs',
        'bulk_actions', 'bulk_action_records', 'media', 'attendance_summaries',
        'payroll_allowance_snapshots', 'payroll_deduction_snapshots', 'payroll_bonus_snapshots',
    ],

    /*
    | One shared account per role, listed on the login page in this order (the
    | first is pre-filled). Roles must exist (ShieldSeeder); DemoAccountSeeder
    | creates or refreshes these users and their employee records.
    */
    'accounts' => [
        [
            'role' => 'Admin',
            'name' => 'Olumide Adebayo',
            'email' => 'olumide_adebayo@example.com',
            'summary' => 'Full access: every module, users and roles, the audit log and organisation dashboards.',
        ],
        [
            'role' => 'HR Manager',
            'name' => 'Adeola Afolabi',
            'email' => 'adeola_afolabi@example.com',
            'summary' => 'Runs people operations: employees, attendance, leave approvals, recruitment and payroll drafts.',
        ],
        [
            'role' => 'Finance Manager',
            'name' => 'Bolanle Adebayo',
            'email' => 'bolanle_adebayo@example.com',
            'summary' => 'Owns pay: pay scales, allowances, deductions, bonuses, payroll approval and payslips.',
        ],
        [
            'role' => 'Department Head',
            'name' => 'Abdul Rahman',
            'email' => 'abdul_rahman@example.com',
            'summary' => 'Leads a team: approves or rejects their leave and maintains employees, departments and designations.',
        ],
        [
            'role' => 'Employee',
            'name' => 'Ugochukwu Okonkwo',
            'email' => 'ugochukwu_okonkwo@example.com',
            'summary' => 'Self-service: clock in and out, request leave, check balances and download own payslips.',
        ],
        [
            'role' => 'IT Admin',
            'name' => 'Olumide Adeyemi',
            'email' => 'olumide_adeyemi@example.com',
            'summary' => 'Same self-service access as Employee; a separate role ready for IT-specific permissions.',
        ],
    ],
];
