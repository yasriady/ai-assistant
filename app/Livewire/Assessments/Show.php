<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Assessment')]
class Show extends Component
{
    use AuthorizesRequests;

    public Assessment $assessment;

    public function mount(Assessment $assessment): void
    {
        $this->authorize('view', $assessment);
        $this->assessment = $assessment->load([
            'course',
            'rubric',
            'submissions.student',
            'examQuestions.question',
        ]);
    }

    public function render()
    {
        return view('livewire.assessments.show')
            ->layoutData(['header' => $this->assessment->title]);
    }
}
