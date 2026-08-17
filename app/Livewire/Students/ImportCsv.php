<?php

namespace App\Livewire\Students;

use App\Models\Course;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Import Students')]
class ImportCsv extends Component
{
    use WithFileUploads;

    public $csv;

    public ?int $course_id = null;

    public string $resultMessage = '';

    protected function rules(): array
    {
        return [
            'csv' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
        ];
    }

    public function import(): void
    {
        $this->validate();

        $path = $this->csv->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            $this->addError('csv', __('ui.flash.csv_unreadable'));

            return;
        }

        $header = fgetcsv($handle);
        $created = 0;
        $updated = 0;
        $attached = 0;

        DB::transaction(function () use ($handle, &$created, &$updated, &$attached): void {
            while (($row = fgetcsv($handle)) !== false) {
                if (count(array_filter($row, fn ($v) => $v !== null && $v !== '')) === 0) {
                    continue;
                }

                $nim = trim((string) ($row[0] ?? ''));
                $name = trim((string) ($row[1] ?? ''));
                $email = trim((string) ($row[2] ?? ''));
                $program = trim((string) ($row[3] ?? ''));
                $className = trim((string) ($row[4] ?? ''));

                if ($nim === '' || $name === '') {
                    continue;
                }

                $student = Student::query()->where('nim', $nim)->first();

                if ($student) {
                    $student->update([
                        'name' => $name,
                        'email' => $email !== '' ? $email : $student->email,
                        'program' => $program !== '' ? $program : $student->program,
                        'class_name' => $className !== '' ? $className : $student->class_name,
                    ]);
                    $updated++;
                } else {
                    $student = Student::query()->create([
                        'nim' => $nim,
                        'name' => $name,
                        'email' => $email !== '' ? $email : null,
                        'program' => $program !== '' ? $program : null,
                        'class_name' => $className !== '' ? $className : null,
                    ]);
                    $created++;
                }

                if ($this->course_id) {
                    $student->courses()->syncWithoutDetaching([$this->course_id]);
                    $attached++;
                }
            }
        });

        fclose($handle);

        $this->resultMessage = $this->course_id
            ? __('ui.flash.import_finished_attached', ['created' => $created, 'updated' => $updated, 'attached' => $attached])
            : __('ui.flash.import_finished', ['created' => $created, 'updated' => $updated]);
        session()->flash('success', $this->resultMessage);
        $this->reset('csv');
    }

    public function render()
    {
        $user = Auth::user();

        $courses = Course::query()
            ->forActiveTerm()
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->orderBy('name')
            ->get();

        return view('livewire.students.import-csv', compact('courses'))
            ->layoutData(['header' => __('ui.students.import_header')]);
    }
}
