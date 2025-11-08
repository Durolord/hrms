<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicantController extends Controller
{
    public function downloadCv(Applicant $applicant): StreamedResponse
    {
        if (! $applicant->cv || ! Storage::disk('public')->exists($applicant->cv)) {
            abort(404, 'CV not found');
        }
        $filename = "{$applicant->name}'s CV.pdf";

        return Storage::disk('public')->download($applicant->cv, $filename);
    }
}
