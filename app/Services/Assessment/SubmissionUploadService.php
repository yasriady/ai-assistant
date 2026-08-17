<?php

namespace App\Services\Assessment;

use App\Enums\AssessmentEngine;
use App\Enums\JobProcessStatus;
use App\Enums\SubmissionStatus;
use App\Jobs\AssessAnswerJob;
use App\Jobs\AssessDocumentJob;
use App\Jobs\ExtractDocumentTextJob;
use App\Models\Assessment;
use App\Models\Student;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Support\StudentFilenameParser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SubmissionUploadService
{
    /** @var list<string> */
    protected array $allowedMimes = [
        'application/pdf',
        'application/x-pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/msword',
        'text/plain',
    ];

    /** @var list<string> */
    protected array $allowedExtensions = ['pdf', 'docx', 'doc', 'txt'];

    public function __construct(
        protected StudentFilenameParser $filenameParser,
    ) {}

    /**
     * Validate, store, create submission, and dispatch processing jobs.
     *
     * @param  UploadedFile|list<UploadedFile>  $files
     * @param  array<string, mixed>  $options
     */
    public function upload(Assessment $assessment, UploadedFile|array $files, array $options = []): Submission
    {
        $files = is_array($files) ? $files : [$files];

        if ($files === []) {
            throw new InvalidArgumentException('At least one file is required.');
        }

        foreach ($files as $file) {
            $this->validateFile($file);
        }

        $disk = (string) ($options['disk'] ?? config('filesystems.submission_disk', 'private'));
        $primary = $files[0];
        $parsed = $this->filenameParser->parse($primary->getClientOriginalName());

        $student = $this->resolveStudent($parsed, $options);

        return DB::transaction(function () use ($assessment, $files, $disk, $student, $options) {
            $submission = Submission::query()->create([
                'assessment_id' => $assessment->id,
                'student_id' => $student->id,
                'status' => SubmissionStatus::Uploaded,
            ]);

            foreach ($files as $file) {
                $path = $file->storeAs(
                    "submissions/{$assessment->id}/{$submission->id}",
                    $this->safeFilename($file),
                    $disk,
                );

                if ($path === false) {
                    throw new InvalidArgumentException('Failed to store uploaded file.');
                }

                SubmissionFile::query()->create([
                    'submission_id' => $submission->id,
                    'original_name' => $file->getClientOriginalName(),
                    'disk' => $disk,
                    'path' => $path,
                    'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
                    'size' => $file->getSize() ?: 0,
                    'extraction_status' => JobProcessStatus::Pending,
                ]);
            }

            $this->dispatchJobs($submission->fresh(['files', 'assessment']), $options);

            return $submission->fresh(['files', 'student']);
        });
    }

    protected function validateFile(UploadedFile $file): void
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType() ?: $file->getClientMimeType();

        if (! in_array($extension, $this->allowedExtensions, true)) {
            throw new InvalidArgumentException(
                "Unsupported file extension [{$extension}]. Allowed: ".implode(', ', $this->allowedExtensions)
            );
        }

        if ($mime && ! in_array($mime, $this->allowedMimes, true) && ! str_starts_with((string) $mime, 'text/')) {
            // Some browsers send odd mimes for docx; extension check is primary.
            if (! in_array($extension, ['docx', 'doc', 'pdf', 'txt'], true)) {
                throw new InvalidArgumentException("Unsupported mime type [{$mime}].");
            }
        }

        $maxKb = (int) config('filesystems.submission_max_kb', 10240);
        if ($file->getSize() > $maxKb * 1024) {
            throw new InvalidArgumentException("File exceeds maximum size of {$maxKb} KB.");
        }
    }

    /**
     * @param  array{nim: string|null, name: string|null, original: string}  $parsed
     * @param  array<string, mixed>  $options
     */
    protected function resolveStudent(array $parsed, array $options): Student
    {
        if (! empty($options['student_id'])) {
            return Student::query()->findOrFail($options['student_id']);
        }

        $nim = $options['nim'] ?? $parsed['nim'] ?? null;
        if (! $nim) {
            throw new InvalidArgumentException(
                'Unable to resolve student NIM from filename. Expected pattern like 230101001_Name.pdf'
            );
        }

        $student = Student::query()->where('nim', $nim)->first();

        if ($student) {
            if (! empty($parsed['name']) && blank($student->name)) {
                $student->update(['name' => $parsed['name']]);
            }

            return $student;
        }

        if (! ($options['create_student'] ?? true)) {
            throw new InvalidArgumentException("Student with NIM [{$nim}] was not found.");
        }

        return Student::query()->create([
            'nim' => $nim,
            'name' => $parsed['name'] ?: "Student {$nim}",
            'email' => $options['email'] ?? null,
        ]);
    }

    protected function safeFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $base = Str::slug($base) ?: 'upload';

        return $base.'_'.Str::random(8).'.'.$extension;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    protected function dispatchJobs(Submission $submission, array $options): void
    {
        if (($options['dispatch'] ?? true) === false) {
            return;
        }

        $engine = $submission->assessment?->engine ?? $submission->assessment?->type?->engine();

        if ($engine === AssessmentEngine::Exam) {
            // Exam answers are typically posted via API; if files exist, still extract then assess answers.
            ExtractDocumentTextJob::dispatch($submission->id);

            foreach ($submission->answers as $answer) {
                AssessAnswerJob::dispatch($answer->id);
            }

            return;
        }

        ExtractDocumentTextJob::dispatch($submission->id);
        // AssessDocumentJob is chained from ExtractDocumentTextJob on success.
    }
}
