<?php

namespace Tests\Unit;

use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Course;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use App\Services\Assessment\DeterministicGradingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeterministicGradingServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_grades_correct_mcq_with_full_marks(): void
    {
        [$question, $correct] = $this->makeMcq();

        $answer = new Answer([
            'question_id' => $question->id,
            'selected_option_id' => $correct->id,
            'max_score' => 5,
        ]);
        $answer->setRelation('question', $question->load('options'));
        $answer->setRelation('selectedOption', $correct);

        $result = (new DeterministicGradingService)->grade($answer, $question);

        $this->assertTrue($result['is_correct']);
        $this->assertSame(5.0, $result['score']);
        $this->assertSame(5.0, $result['max_score']);
    }

    #[Test]
    public function it_grades_incorrect_mcq_as_zero(): void
    {
        [$question] = $this->makeMcq();
        $wrong = $question->options->firstWhere('is_correct', false);

        $answer = new Answer([
            'question_id' => $question->id,
            'selected_option_id' => $wrong->id,
            'max_score' => 5,
        ]);
        $answer->setRelation('question', $question);
        $answer->setRelation('selectedOption', $wrong);

        $result = (new DeterministicGradingService)->grade($answer, $question);

        $this->assertFalse($result['is_correct']);
        $this->assertSame(0.0, $result['score']);
    }

    #[Test]
    public function it_grades_true_false_from_answer_text(): void
    {
        $user = User::factory()->create();
        $course = Course::query()->create([
            'user_id' => $user->id,
            'code' => 'TF101',
            'name' => 'TF Course',
        ]);

        $question = Question::query()->create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'question_type' => QuestionType::TrueFalse,
            'question_text' => 'True statement?',
            'expected_answer' => 'benar',
            'max_score' => 2,
        ]);
        $question->setRelation('options', collect());

        $answer = new Answer([
            'question_id' => $question->id,
            'answer_text' => 'true',
            'max_score' => 2,
        ]);

        $result = (new DeterministicGradingService)->grade($answer, $question);

        $this->assertTrue($result['is_correct']);
        $this->assertSame(2.0, $result['score']);
        $this->assertSame('boolean_compare', $result['method']);
    }

    #[Test]
    public function it_rejects_essay_questions(): void
    {
        $user = User::factory()->create();
        $course = Course::query()->create([
            'user_id' => $user->id,
            'code' => 'ES101',
            'name' => 'Essay Course',
        ]);

        $question = Question::query()->create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'question_type' => QuestionType::Essay,
            'question_text' => 'Explain polymorphism.',
            'max_score' => 10,
        ]);

        $answer = new Answer(['question_id' => $question->id, 'answer_text' => '...']);

        $this->expectException(InvalidArgumentException::class);
        (new DeterministicGradingService)->grade($answer, $question);
    }

    /**
     * @return array{0: Question, 1: QuestionOption}
     */
    protected function makeMcq(): array
    {
        $user = User::factory()->create();
        $course = Course::query()->create([
            'user_id' => $user->id,
            'code' => 'MC101',
            'name' => 'MCQ Course',
        ]);

        $question = Question::query()->create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'question_type' => QuestionType::MultipleChoice,
            'question_text' => 'Pick B',
            'max_score' => 5,
        ]);

        QuestionOption::query()->create([
            'question_id' => $question->id,
            'label' => 'A',
            'option_text' => 'Wrong',
            'is_correct' => false,
            'order_index' => 0,
        ]);

        $correct = QuestionOption::query()->create([
            'question_id' => $question->id,
            'label' => 'B',
            'option_text' => 'Right',
            'is_correct' => true,
            'order_index' => 1,
        ]);

        $question->load('options');

        return [$question, $correct];
    }
}
