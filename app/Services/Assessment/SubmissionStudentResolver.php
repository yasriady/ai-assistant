<?php

namespace App\Services\Assessment;

use App\Models\Student;
use App\Models\Submission;
use App\Services\AI\AIManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubmissionStudentResolver
{
    public function __construct(
        protected StudentDocumentIdentityExtractor $identityExtractor,
        protected AIManager $ai,
    ) {}

    public function createPlaceholderStudent(string $label): Student
    {
        return Student::query()->create([
            'nim' => 'PENDING-'.Str::upper(Str::random(10)),
            'name' => $label,
        ]);
    }

    public function isPlaceholderStudent(Student $student): bool
    {
        return str_starts_with($student->nim, 'PENDING-');
    }

    public function resolve(Submission $submission): Submission
    {
        $submission->loadMissing(['student', 'assessment.course']);

        if (blank($submission->extracted_text)) {
            return $submission;
        }

        if (! $submission->student || ! $this->isPlaceholderStudent($submission->student)) {
            return $submission;
        }

        $identity = $this->identityExtractor->extract($submission->extracted_text);

        if (blank($identity['nim']) || $identity['confidence'] < 0.75) {
            $identity = $this->mergeIdentity($identity, $this->extractWithAi($submission));
        }

        if (blank($identity['nim']) && blank($identity['name'])) {
            return $submission;
        }

        if (blank($identity['nim']) && filled($identity['name'])) {
            $matched = $this->matchStudentByName($submission, (string) $identity['name']);
            if ($matched) {
                $identity['nim'] = $matched->nim;
                $identity['name'] = $matched->name;
            }
        }

        return $this->assignStudent($submission, $identity);
    }

    protected function matchStudentByName(Submission $submission, string $name): ?Student
    {
        $course = $submission->assessment?->course;
        if (! $course) {
            return null;
        }

        $normalized = mb_strtolower(trim($name));
        $students = $course->students()->get();

        foreach ($students as $student) {
            if (mb_strtolower(trim($student->name)) === $normalized) {
                return $student;
            }
        }

        foreach ($students as $student) {
            if (str_contains(mb_strtolower(trim($student->name)), $normalized)
                || str_contains($normalized, mb_strtolower(trim($student->name)))) {
                return $student;
            }
        }

        return null;
    }

    /**
     * @param  array{nim: string|null, name: string|null, confidence?: float, source?: string}  $identity
     */
    protected function assignStudent(Submission $submission, array $identity): Submission
    {
        $nim = trim((string) ($identity['nim'] ?? ''));
        $name = trim((string) ($identity['name'] ?? ''));

        if ($nim === '' && $name === '') {
            return $submission;
        }

        if ($nim === '') {
            $submission->student?->update([
                'name' => $name !== '' ? $name : $submission->student->name,
            ]);

            return $submission->fresh(['student']);
        }

        return DB::transaction(function () use ($submission, $nim, $name): Submission {
            $course = $submission->assessment?->course;
            $placeholder = $submission->student;

            $student = Student::query()->where('nim', $nim)->first();

            if ($student) {
                if ($name !== '' && (blank($student->name) || $this->isPlaceholderStudent($student))) {
                    $student->update(['name' => $name]);
                }
            } else {
                $student = Student::query()->create([
                    'nim' => $nim,
                    'name' => $name !== '' ? $name : "Mahasiswa {$nim}",
                ]);
            }

            if ($course) {
                $course->students()->syncWithoutDetaching([$student->id]);
            }

            $existing = Submission::query()
                ->where('assessment_id', $submission->assessment_id)
                ->where('student_id', $student->id)
                ->whereKeyNot($submission->id)
                ->first();

            if ($existing) {
                $submission->files()->update(['submission_id' => $existing->id]);

                if ($placeholder && $this->isPlaceholderStudent($placeholder)) {
                    $submission->delete();
                    $this->deletePlaceholderIfUnused($placeholder);
                }

                return $existing->fresh(['student', 'files']);
            }

            $submission->update(['student_id' => $student->id]);

            if ($placeholder && $this->isPlaceholderStudent($placeholder)) {
                $this->deletePlaceholderIfUnused($placeholder);
            }

            return $submission->fresh(['student', 'files']);
        });
    }

    /**
     * @param  array{nim: string|null, name: string|null, confidence?: float, source?: string}  $base
     * @param  array{nim?: string|null, name?: string|null, confidence?: float, source?: string}  $extra
     * @return array{nim: string|null, name: string|null, confidence: float, source: string}
     */
    protected function mergeIdentity(array $base, array $extra): array
    {
        $nim = filled($extra['nim'] ?? null) ? (string) $extra['nim'] : ($base['nim'] ?? null);
        $name = filled($extra['name'] ?? null) ? (string) $extra['name'] : ($base['name'] ?? null);
        $confidence = max((float) ($base['confidence'] ?? 0), (float) ($extra['confidence'] ?? 0));
        $source = ($extra['source'] ?? 'none') !== 'none' ? (string) $extra['source'] : (string) ($base['source'] ?? 'none');

        return [
            'nim' => $nim,
            'name' => $name,
            'confidence' => $confidence,
            'source' => $source,
        ];
    }

    /**
     * @return array{nim: string|null, name: string|null, confidence: float, source: string}
     */
    protected function extractWithAi(Submission $submission): array
    {
        try {
            $result = $this->ai->extractStudentIdentity([
                'document_text' => mb_substr((string) $submission->extracted_text, 0, 8000),
            ], [
                'assessment_id' => $submission->assessment_id,
                'submission_id' => $submission->id,
                'method' => 'extractStudentIdentity',
            ]);

            return [
                'nim' => isset($result['nim']) ? trim((string) $result['nim']) : null,
                'name' => isset($result['name']) ? trim((string) $result['name']) : null,
                'confidence' => (float) ($result['confidence'] ?? 0),
                'source' => 'ai',
            ];
        } catch (\Throwable) {
            return [
                'nim' => null,
                'name' => null,
                'confidence' => 0.0,
                'source' => 'none',
            ];
        }
    }

    protected function deletePlaceholderIfUnused(Student $student): void
    {
        if (! $this->isPlaceholderStudent($student)) {
            return;
        }

        if ($student->submissions()->exists()) {
            return;
        }

        $student->delete();
    }
}
