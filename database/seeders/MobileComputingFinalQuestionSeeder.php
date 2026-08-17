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

class MobileComputingFinalQuestionSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array<string, mixed> $data */
        $data = require database_path('seeders/data/mobile_computing_uas.php');

        $course = Course::query()
            ->where('code', $data['course_code'])
            ->where('term_code', $data['term_code'])
            ->first();

        if (! $course) {
            throw new RuntimeException('Course Mobile Computing term 20252 tidak ditemukan.');
        }

        $questionBank = QuestionBank::query()
            ->where('course_id', $course->id)
            ->where(function ($query) use ($data): void {
                $query->where('name', $data['question_bank_name'])
                    ->orWhere('name', 'Soal UTS Mobile Computing');
            })
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
            'purpose' => QuestionBankPurpose::Final,
        ])->save();

        $uasTopicId = $course->topics()
            ->where('week_number', 16)
            ->value('id');

        DB::transaction(function () use ($data, $course, $questionBank, $uasTopicId): void {
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
                            'source_exam' => 'UAS Mobile Computing',
                            'source_number' => $item['number'],
                        ],
                    ]
                );

                if ($uasTopicId) {
                    $question->courseTopics()->sync([$uasTopicId]);
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
