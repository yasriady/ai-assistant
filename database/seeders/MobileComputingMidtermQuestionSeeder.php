<?php

namespace Database\Seeders;

use App\Enums\Difficulty;
use App\Enums\QuestionBankPurpose;
use App\Enums\QuestionScopeType;
use App\Enums\QuestionType;
use App\Models\Course;
use App\Models\Question;
use App\Models\QuestionBank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MobileComputingMidtermQuestionSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array<string, mixed> $data */
        $data = require database_path('seeders/data/mobile_computing_uts.php');

        $course = Course::query()
            ->where('code', $data['course_code'])
            ->where('term_code', $data['term_code'])
            ->first();

        if (! $course) {
            throw new RuntimeException('Course Mobile Computing term 20252 tidak ditemukan.');
        }

        $questionBank = QuestionBank::query()
            ->where('course_id', $course->id)
            ->where('name', $data['question_bank_name'])
            ->first();

        if (! $questionBank) {
            $questionBank = new QuestionBank([
                'course_id' => $course->id,
                'user_id' => $course->user_id,
            ]);
        }

        $questionBank->fill([
            'user_id' => $course->user_id,
            'name' => $data['question_bank_name'],
            'description' => $data['question_bank_description'],
            'purpose' => QuestionBankPurpose::Midterm,
        ])->save();

        $utsTopicId = $course->topics()
            ->where('week_number', 8)
            ->value('id');

        DB::transaction(function () use ($data, $course, $questionBank, $utsTopicId): void {
            foreach ($data['questions'] as $item) {
                $questionType = QuestionType::from($item['type']);

                $question = Question::query()->updateOrCreate(
                    [
                        'question_bank_id' => $questionBank->id,
                        'question_text' => $item['question_text'],
                    ],
                    [
                        'course_id' => $course->id,
                        'user_id' => $course->user_id,
                        'topic' => $data['topic'],
                        'scope_type' => QuestionScopeType::Specific,
                        'question_type' => $questionType,
                        'expected_answer' => $item['expected_answer'] ?? null,
                        'key_concepts' => $item['key_concepts'] ?? null,
                        'difficulty' => Difficulty::from($item['difficulty']),
                        'cognitive_level' => $item['cognitive_level'],
                        'max_score' => $item['max_score'],
                        'metadata' => [
                            'source_file' => $data['source_file'],
                            'source_exam' => 'UTS Mobile Computing',
                            'source_number' => $item['number'],
                        ],
                    ]
                );

                if ($utsTopicId) {
                    $question->courseTopics()->sync([$utsTopicId]);
                }

                if ($questionType === QuestionType::MultipleChoice) {
                    $question->options()->delete();

                    foreach ($item['options'] as $index => $option) {
                        $question->options()->create([
                            'label' => $option['label'],
                            'option_text' => $option['option_text'],
                            'is_correct' => $option['is_correct'],
                            'order_index' => $index,
                        ]);
                    }
                }
            }
        });
    }
}
