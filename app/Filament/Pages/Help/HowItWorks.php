<?php

namespace App\Filament\Pages\Help;

class HowItWorks extends HelpPage
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'How it works';

    protected static ?string $title = 'How it works';

    protected static ?string $slug = 'help/how-it-works';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.help.how-it-works';

    /**
     * What each seeded role can do, summarised from its permissions (ShieldSeeder, ExtraPermissionsSeeder).
     *
     * @return array<string, list<string>>
     */
    public function roles(): array
    {
        return [
            'Admin' => [
                'Everything below, across every branch.',
                'Manage users and their roles, and edit role permissions.',
                'Read the audit log, general settings and the organisation dashboards (headcount, payroll trend, distribution).',
            ],
            'HR Manager' => [
                'Create, edit and deactivate employees; manage branches, departments, designations, skills and holidays.',
                'Approve, reject or override any leave request and manage leave types.',
                'Run recruitment: openings, the applicant pipeline and hiring.',
                'Prepare payroll: pay scales, allowances, bonuses, deductions and payroll drafts.',
            ],
            'Finance Manager' => [
                'Manage pay scales, allowances, deductions and bonuses.',
                'Generate, regenerate, approve and pay payrolls; export bank transfer files and payslips.',
                'View employees, departments, designations and leave (read only), and read the audit log.',
            ],
            'Department Head' => [
                'Approve or reject leave for the people who report to them in their branch.',
                'Maintain employees, departments and designations.',
                'Self-service: own attendance, leave and payslips.',
            ],
            'Employee' => [
                'Mark attendance (time in, breaks, time out) for today.',
                'Request leave, see the remaining balance and cancel a request while it is pending.',
                'View and download their own approved payslips and documents.',
            ],
            'IT Admin' => [
                'The same self-service access as Employee.',
                'A separate role so IT-specific permissions can be granted later in Roles.',
            ],
        ];
    }
}
