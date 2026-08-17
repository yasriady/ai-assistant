<?php

namespace Tests\Feature;

use App\Enums\AssessmentEngine;
use App\Enums\AssessmentType;
use App\Enums\QuestionScopeType;
use App\Enums\QuestionType;
use App\Enums\UserRole;
use App\Livewire\Courses\Rps;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseTopic;
use App\Models\Cpmk;
use App\Models\Question;
use App\Models\User;
use App\Services\Questions\QuestionScopeFilter;
use App\Support\AcademicTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RpsAndQuestionScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function lecturer(): User
    {
        return User::factory()->create([
            'role' => UserRole::Lecturer,
            'active_term_code' => AcademicTerm::current(),
        ]);
    }

    #[Test]
    public function lecturer_can_save_rps_for_course(): void
    {
        $lecturer = $this->lecturer();
        $course = Course::query()->create([
            'user_id' => $lecturer->id,
            'code' => 'IF2201',
            'name' => 'Struktur Data',
            'term_code' => AcademicTerm::current(),
            'midterm_week' => 8,
            'is_active' => true,
        ]);

        Livewire::actingAs($lecturer)
            ->test(Rps::class, ['course' => $course])
            ->set('midterm_week', 8)
            ->set('cpmks', [
                ['id' => null, 'code' => 'CPMK-1', 'description' => 'Outcome 1', 'order_index' => 0],
            ])
            ->set('topics', [
                ['id' => null, 'week_number' => 2, 'title' => 'Pengenalan', 'description' => '', 'order_index' => 0, 'cpmk_ids' => []],
                ['id' => null, 'week_number' => 10, 'title' => 'Lanjut', 'description' => '', 'order_index' => 1, 'cpmk_ids' => []],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('course_topics', [
            'course_id' => $course->id,
            'week_number' => 2,
            'title' => 'Pengenalan',
        ]);

        $this->assertDatabaseHas('cpmks', [
            'course_id' => $course->id,
            'code' => 'CPMK-1',
        ]);
    }

    #[Test]
    public function midterm_filter_excludes_late_semester_topics(): void
    {
        $lecturer = $this->lecturer();
        $course = Course::query()->create([
            'user_id' => $lecturer->id,
            'code' => 'IF2201',
            'name' => 'Struktur Data',
            'term_code' => AcademicTerm::current(),
            'midterm_week' => 8,
            'is_active' => true,
        ]);

        $earlyTopic = CourseTopic::query()->create([
            'course_id' => $course->id,
            'week_number' => 4,
            'title' => 'Early',
            'order_index' => 0,
        ]);

        $lateTopic = CourseTopic::query()->create([
            'course_id' => $course->id,
            'week_number' => 12,
            'title' => 'Late',
            'order_index' => 1,
        ]);

        $earlyQuestion = Question::query()->create([
            'course_id' => $course->id,
            'user_id' => $lecturer->id,
            'question_type' => QuestionType::MultipleChoice,
            'scope_type' => QuestionScopeType::Specific,
            'question_text' => 'Early question',
            'max_score' => 5,
        ]);
        $earlyQuestion->courseTopics()->sync([$earlyTopic->id]);

        $lateQuestion = Question::query()->create([
            'course_id' => $course->id,
            'user_id' => $lecturer->id,
            'question_type' => QuestionType::MultipleChoice,
            'scope_type' => QuestionScopeType::Specific,
            'question_text' => 'Late question',
            'max_score' => 5,
        ]);
        $lateQuestion->courseTopics()->sync([$lateTopic->id]);

        $generalQuestion = Question::query()->create([
            'course_id' => $course->id,
            'user_id' => $lecturer->id,
            'question_type' => QuestionType::Essay,
            'scope_type' => QuestionScopeType::General,
            'question_text' => 'General question',
            'max_score' => 10,
        ]);

        $assessment = Assessment::query()->create([
            'course_id' => $course->id,
            'user_id' => $lecturer->id,
            'title' => 'UTS',
            'type' => AssessmentType::MidtermExam,
            'engine' => AssessmentEngine::Exam,
            'max_score' => 100,
            'status' => 'draft',
        ]);

        $filter = app(QuestionScopeFilter::class);
        $ids = $filter->apply(Question::query(), $assessment)->pluck('id')->all();

        $this->assertContains($earlyQuestion->id, $ids);
        $this->assertContains($generalQuestion->id, $ids);
        $this->assertNotContains($lateQuestion->id, $ids);
    }
}
