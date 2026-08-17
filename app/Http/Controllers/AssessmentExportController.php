<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssessmentExportController extends Controller
{
    public function __invoke(Request $request, Assessment $assessment): StreamedResponse
    {
        Gate::authorize('view', $assessment);

        $assessment->load(['submissions.student', 'course']);

        $filename = 'assessment-'.$assessment->id.'-results-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($assessment): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'NIM',
                'Student Name',
                'Status',
                'AI Score',
                'Final Score',
                'Processed At',
                'Reviewed At',
                'Finalized At',
            ]);

            foreach ($assessment->submissions as $submission) {
                fputcsv($handle, [
                    $submission->student?->nim,
                    $submission->student?->name,
                    $submission->status?->value ?? $submission->status,
                    $submission->ai_score,
                    $submission->final_score,
                    optional($submission->processed_at)?->toDateTimeString(),
                    optional($submission->reviewed_at)?->toDateTimeString(),
                    optional($submission->finalized_at)?->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
