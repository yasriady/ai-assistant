<?php

namespace App\Livewire\Assessments;

use App\Enums\JobProcessStatus;
use App\Enums\SubmissionStatus;
use App\Jobs\ExtractDocumentTextJob;
use App\Models\Assessment;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Services\Assessment\SubmissionStudentResolver;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
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

    public function save(SubmissionStudentResolver $studentResolver): void
    {
        $this->validate();
        $this->authorize('update', $this->assessment);

        $uploaded = 0;
        /** @var list<int> */
        $submissionIds = [];

        DB::transaction(function () use (&$uploaded, &$submissionIds, $studentResolver): void {
            foreach ($this->files as $file) {
                $original = $file->getClientOriginalName();

                $student = $studentResolver->createPlaceholderStudent(
                    __('ui.upload.pending_student', ['file' => $original]),
                );

                $this->assessment->course->students()->syncWithoutDetaching([$student->id]);

                $submission = Submission::query()->create([
                    'assessment_id' => $this->assessment->id,
                    'student_id' => $student->id,
                    'status' => SubmissionStatus::Uploaded,
                ]);

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

                $submissionIds[] = $submission->id;
                $uploaded++;
            }
        });

        foreach (array_unique($submissionIds) as $submissionId) {
            ExtractDocumentTextJob::dispatch($submissionId);
        }

        session()->flash('success', __('ui.flash.files_uploaded', ['count' => $uploaded]));
        $this->reset('files');
        $this->redirect(route('assessments.show', $this->assessment), navigate: true);
    }

    public function render()
    {
        return view('livewire.assessments.upload-submissions')
            ->layoutData(['header' => __('ui.upload.title')]);
    }
}
