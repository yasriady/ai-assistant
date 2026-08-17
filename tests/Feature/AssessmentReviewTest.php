<?php

namespace Tests\Feature;

use App\Enums\AssessmentEngine;
use App\Enums\AssessmentType;
use App\Enums\JobProcessStatus;
use App\Enums\SubmissionStatus;
use App\Enums\UserRole;
use App\Livewire\Assessments\ReviewSubmission;
use App\Models\AiAssessment;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Student;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AssessmentReviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: Assessment, 2: Submission}
     */
    protected function seedReviewContext(): array
    {
        $lecturer = User::factory()->create(['role' => UserRole::Lecturer]);

        $course = Course::query()->create([
            'user_id' => $lecturer->id,
            'code' => 'IF2101',
            'name' => 'Review Course',
            'term_code' => \App\Support\AcademicTerm::current(),
        ]);

        $assessment = Assessment::query()->create([
            'course_id' => $course->id,
            'user_id' => $lecturer->id,
            'title' => 'Demo Assignment',
            'type' => AssessmentType::Assignment,
            'engine' => AssessmentEngine::Document,
            'max_score' => 100,
            'status' => 'published',
        ]);

        $student = Student::query()->create([
            'nim' => '230101001',
            'name' => 'Andi Pratama',
        ]);

        $course->students()->attach($student);

        $submission = Submission::query()->create([
            'assessment_id' => $assessment->id,
            'student_id' => $student->id,
            'status' => SubmissionStatus::Assessed,
            'extracted_text' => 'Sample extracted student text for review.',
            'ai_score' => 80,
            'processed_at' => now(),
        ]);

        AiAssessment::query()->create([
            'submission_id' => $submission->id,
            'provider' => 'null',
            'model' => 'null-mock',
            'status' => JobProcessStatus::Completed,
            'score' => 80,
            'max_score' => 100,
            'confidence' => 0.7,
            'overall_feedback' => 'Demo AI feedback.',
        ]);

        return [$lecturer, $assessment, $submission];
    }

    #[Test]
    public function lecturer_can_open_review_page(): void
    {
        [$lecturer, $assessment, $submission] = $this->seedReviewContext();

        $this->actingAs($lecturer)
            ->get(route('assessments.review', [$assessment, $submission]))
            ->assertOk();
    }

    #[Test]
    public function lecturer_can_mark_submission_as_reviewed(): void
    {
        [$lecturer, $assessment, $submission] = $this->seedReviewContext();

        Livewire::actingAs($lecturer)
            ->test(ReviewSubmission::class, [
                'assessment' => $assessment,
                'submission' => $submission,
            ])
            ->set('final_score', 85)
            ->set('feedback', 'Good work with minor revisions.')
            ->call('markReviewed')
            ->assertHasNoErrors();

        $submission->refresh();

        $this->assertSame(SubmissionStatus::Reviewed, $submission->status);
        $this->assertEquals(85.0, (float) $submission->final_score);
    }

    #[Test]
    public function lecturer_can_finalize_submission(): void
    {
        [$lecturer, $assessment, $submission] = $this->seedReviewContext();

        Livewire::actingAs($lecturer)
            ->test(ReviewSubmission::class, [
                'assessment' => $assessment,
                'submission' => $submission,
            ])
            ->set('final_score', 88)
            ->set('feedback', 'Finalized after review.')
            ->set('lecturer_notes', 'Accepted with small adjustment.')
            ->call('finalize')
            ->assertHasNoErrors()
            ->assertRedirect(route('assessments.show', $assessment));

        $submission->refresh();

        $this->assertSame(SubmissionStatus::Finalized, $submission->status);
        $this->assertDatabaseHas('final_assessments', [
            'submission_id' => $submission->id,
            'score' => 88,
            'finalized_by' => $lecturer->id,
        ]);
    }
}
