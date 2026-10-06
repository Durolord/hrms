<?php

use App\Filament\Resources\OpeningResource\Pages\ShowOpening;
use App\Http\Controllers\ApplicantController;
use App\Models\Applicant;
use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/jobs/{record}', ShowOpening::class)->name('jobs.apply');

// Private documents: require a logged-in user and the matching policy ability.
Route::middleware(Authenticate::class)->group(function () {
    Route::get('/download-cv/{applicant}', [ApplicantController::class, 'downloadCv'])
        ->can('viewAny', Applicant::class)
        ->name('applicant.download-cv');

    Route::get('/payroll/{payroll}/download-pdf', function (Payroll $payroll) {
        $pdf = Pdf::loadView('pdf.payroll-slip', compact('payroll'));
        $employeeSlug = Str::slug($payroll->employee->name);
        $payrollMonth = \Carbon\Carbon::parse($payroll->month)->format('F-Y');
        $filename = "{$employeeSlug}-{$payrollMonth}.pdf";

        return $pdf->download($filename);
    })->can('view', 'payroll')->name('payroll.download-pdf');
});

Route::get('/jobs', App\Filament\Pages\Applicants\JobOpenings::class)->name('jobs.show');
