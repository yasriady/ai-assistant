<?php

namespace App\Livewire\Assessments;

use App\Enums\JobProcessStatus;
use App\Enums\SubmissionStatus;
use App\Models\Assessment;
use App\Models\Student;
use App\Models\Submission;
use App\Models\SubmissionFile;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Upload Submissions')]
class UploadSubmissions extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public Assessment $assessment;

    /** @var list<\Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $files = [];

    public string $namingHint = '';

    public function mount(Assessment $assessment): void
    {
        $this->authorize('update', $assessment);
        $this->assessment = $assessment->load('course.students');
        $this->namingHint = __('ui.upload.naming_hint');
    }

    protected function rules(): array
    {
        return [
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx,txt,zip,png,jpg,jpeg'],
        ];
    }

    public function save(): void
    {
        $this->validate();
        $this->authorize('update', $this->assessment);

        $uploaded = 0;

        DB::transaction(function () use (&$uploaded): void {
            foreach ($this->files as $file) {
                $original = $file->getClientOriginalName();
                $nim = $this->extractNim($original);
                $student = null;

                if ($nim) {
                    $student = Student::query()->where('nim', $nim)->first();
                }

                if (! $student) {
                    $student = Student::query()->firstOrCreate(
                        ['nim' => $nim ?: 'UNK-'.Str::upper(Str::random(6))],
                        ['name' => pathinfo($original, PATHINFO_FILENAME)],
                    );

                    $this->assessment->course->students()->syncWithoutDetaching([$student->id]);
                }

                $submission = Submission::query()->firstOrCreate(
                    [
                        'assessment_id' => $this->assessment->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'status' => SubmissionStatus::Uploaded,
                    ],
                );

                $path = $file->store('submissions/'.$this->assessment->id, config('filesystems.submission_disk', 'private'));

                SubmissionFile::query()->create([
                    'submission_id' => $submission->id,
                    'original_name' => $original,
                    'disk' => config('filesystems.submission_disk', 'private'),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'extraction_status' => JobProcessStatus::Pending,
                ]);

                $submission->update(['status' => SubmissionStatus::Uploaded]);
                $uploaded++;
            }
        });

        session()->flash('success', __('ui.flash.files_uploaded', ['count' => $uploaded]));
        $this->reset('files');
        $this->redirect(route('assessments.show', $this->assessment), navigate: true);
    }

    private function extractNim(string $filename): ?string
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);

        if (preg_match('/^([A-Za-z0-9\-\.]+)/', $base, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function render()
    {
        return view('livewire.assessments.upload-submissions')
            ->layoutData(['header' => __('ui.upload.title')]);
    }
}
