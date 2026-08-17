<?php

namespace App\Services\Export;

use App\Exports\AssessmentSubmissionsExport;
use App\Models\Assessment;
use App\Models\Submission;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssessmentExportService
{
    /**
     * Build rows for export.
     *
     * @return Collection<int, array<int, scalar|null>>
     */
    public function rows(Assessment $assessment): Collection
    {
        $assessment->loadMissing([
            'submissions.student',
            'submissions.finalAssessment',
        ]);

        return $assessment->submissions
            ->sortBy(fn (Submission $s) => $s->student?->nim)
            ->values()
            ->map(function (Submission $submission) {
                return [
                    $submission->student?->nim,
                    $submission->student?->name,
                    $submission->status?->value,
                    $submission->ai_score,
                    $submission->final_score,
                    $submission->finalAssessment?->score,
                    $submission->processed_at?->toDateTimeString(),
                    $submission->reviewed_at?->toDateTimeString(),
                    $submission->finalized_at?->toDateTimeString(),
                ];
            });
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'NIM',
            'Name',
            'Status',
            'AI Score',
            'Final Score',
            'Finalized Score',
            'Processed At',
            'Reviewed At',
            'Finalized At',
        ];
    }

    public function toCsv(Assessment $assessment): string
    {
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open temp stream for CSV export.');
        }

        fputcsv($handle, $this->headings());

        foreach ($this->rows($assessment) as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $csv;
    }

    public function downloadCsv(Assessment $assessment, ?string $filename = null): StreamedResponse
    {
        $filename ??= 'assessment_'.$assessment->id.'_submissions.csv';
        $csv = $this->toCsv($assessment);

        return response()->streamDownload(function () use ($csv): void {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Create an XLSX file on disk using PhpSpreadsheet (reliable across Excel package versions).
     */
    public function storeXlsx(Assessment $assessment, string $absolutePath): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($this->headings(), null, 'A1');

        $rowIndex = 2;
        foreach ($this->rows($assessment) as $row) {
            $sheet->fromArray(array_values($row), null, 'A'.$rowIndex);
            $rowIndex++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($absolutePath);
        $spreadsheet->disconnectWorksheets();

        return $absolutePath;
    }

    public function downloadXlsx(Assessment $assessment, ?string $filename = null): StreamedResponse
    {
        $filename ??= 'assessment_'.$assessment->id.'_submissions.xlsx';
        $temp = tempnam(sys_get_temp_dir(), 'assess_xlsx_');
        if ($temp === false) {
            throw new \RuntimeException('Unable to create temp file for XLSX export.');
        }

        $path = $temp.'.xlsx';
        @unlink($temp);
        $this->storeXlsx($assessment, $path);

        return response()->streamDownload(function () use ($path): void {
            echo file_get_contents($path);
            @unlink($path);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Optional Maatwebsite Excel export instance when the package facade is preferred.
     */
    public function excelExport(Assessment $assessment): AssessmentSubmissionsExport
    {
        return new AssessmentSubmissionsExport($this->headings(), $this->rows($assessment)->all());
    }
}
