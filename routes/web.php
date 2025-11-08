<?php

use App\Filament\Resources\OpeningResource\Pages\ShowOpening;
use App\Http\Controllers\ApplicantController;
use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

Route::get('/jobs/{record}', ShowOpening::class)->name('jobs.apply');
Route::get('/download-cv/{applicant}', [ApplicantController::class, 'downloadCv'])
    ->name('applicant.download-cv');
Route::get('/payroll/{payroll}/download-pdf', function (Payroll $payroll) {
    $pdf = Pdf::loadView('pdf.payroll-slip', compact('payroll'));
    $employeeSlug = Str::slug($payroll->employee->name);
    $payrollMonth = \Carbon\Carbon::parse($payroll->month)->format('F-Y');
    $filename = "{$employeeSlug}-{$payrollMonth}.pdf";

    return $pdf->download($filename);
})->name('payroll.download-pdf');
Route::get('/jobs', App\Filament\Pages\Applicants\JobOpenings::class)->name('jobs.show');
