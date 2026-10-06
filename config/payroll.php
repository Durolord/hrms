<?php

return [
    /*
     * Statutory deductions calculated automatically when a payroll is generated.
     * Each value is a percentage of the (prorated) basic salary; 0 disables the line.
     * Defaults are 0 so nothing changes until the organisation configures them.
     */
    'statutory' => [
        'Pension' => (float) env('PAYROLL_PENSION_RATE', 0),
        'Tax (PAYE)' => (float) env('PAYROLL_TAX_RATE', 0),
    ],

    // Day of the month on which draft payrolls are generated for the current month.
    'generate_on_day' => (int) env('PAYROLL_GENERATE_DAY', 20),
];
