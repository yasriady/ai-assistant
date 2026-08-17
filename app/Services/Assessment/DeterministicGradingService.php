<?php

namespace App\Services\Assessment;

use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Question;
use App\Models\QuestionOption;
use InvalidArgumentException;

class DeterministicGradingService
{
    /**
     * Grade MCQ / true-false answers exactly (0 or full marks).
     *
     * @return array{
     *     score: float,
     *     max_score: float,
     *     is_correct: bool,
     *     method: string,
     *     details: array<string, mixed>
     * }
     */
    public function grade(Answer $answer, ?Question $question = null): array
    {
        $question ??= $answer->question()->with('options')->first();

        if (! $question instanceof Question) {
            throw new InvalidArgumentException('Answer has no associated question.');
        }

        $maxScore = (float) ($answer->max_score ?? $question->max_score ?? 1);
        $type = $question->question_type;

        return match ($type) {
            QuestionType::MultipleChoice => $this->gradeMultipleChoice($answer, $question, $maxScore),
            QuestionType::TrueFalse => $this->gradeTrueFalse($answer, $question, $maxScore),
            default => throw new InvalidArgumentException(
                "Deterministic grading is not supported for question type [{$type->value}]."
            ),
        };
    }

    public function supports(QuestionType $type): bool
    {
        return in_array($type, [QuestionType::MultipleChoice, QuestionType::TrueFalse], true);
    }

    /**
     * @return array{score: float, max_score: float, is_correct: bool, method: string, details: array<string, mixed>}
     */
    protected function gradeMultipleChoice(Answer $answer, Question $question, float $maxScore): array
    {
        $correct = $question->options->firstWhere('is_correct', true);
        $selected = $answer->selectedOption
            ?? ($answer->selected_option_id
                ? QuestionOption::query()->find($answer->selected_option_id)
                : null);

        $isCorrect = false;
        $method = 'selected_option';

        if ($selected instanceof QuestionOption) {
            $isCorrect = (bool) $selected->is_correct;
        } elseif (is_string($answer->answer_text) && trim($answer->answer_text) !== '') {
            $method = 'answer_text';
            $isCorrect = $this->textMatchesCorrectOption($answer->answer_text, $question);
        } elseif (is_array($answer->answer_data)) {
            $method = 'answer_data';
            $candidate = $answer->answer_data['option_id']
                ?? $answer->answer_data['selected_option_id']
                ?? $answer->answer_data['label']
                ?? $answer->answer_data['value']
                ?? null;

            if (is_numeric($candidate)) {
                $opt = $question->options->firstWhere('id', (int) $candidate);
                $isCorrect = $opt?->is_correct === true;
            } elseif (is_string($candidate)) {
                $isCorrect = $this->textMatchesCorrectOption($candidate, $question);
            }
        }

        return [
            'score' => $isCorrect ? $maxScore : 0.0,
            'max_score' => $maxScore,
            'is_correct' => $isCorrect,
            'method' => $method,
            'details' => [
                'correct_option_id' => $correct?->id,
                'selected_option_id' => $selected?->id ?? $answer->selected_option_id,
            ],
        ];
    }

    /**
     * @return array{score: float, max_score: float, is_correct: bool, method: string, details: array<string, mixed>}
     */
    protected function gradeTrueFalse(Answer $answer, Question $question, float $maxScore): array
    {
        // Prefer option-based true/false when options exist.
        if ($question->options->isNotEmpty()) {
            return $this->gradeMultipleChoice($answer, $question, $maxScore);
        }

        $expected = $this->normalizeBoolean($question->expected_answer);
        $given = $this->normalizeBoolean($answer->answer_text);

        if ($given === null && is_array($answer->answer_data)) {
            $given = $this->normalizeBoolean(
                $answer->answer_data['value']
                    ?? $answer->answer_data['answer']
                    ?? $answer->answer_data['selected']
                    ?? null
            );
        }

        $isCorrect = $expected !== null && $given !== null && $expected === $given;

        return [
            'score' => $isCorrect ? $maxScore : 0.0,
            'max_score' => $maxScore,
            'is_correct' => $isCorrect,
            'method' => 'boolean_compare',
            'details' => [
                'expected' => $expected,
                'given' => $given,
            ],
        ];
    }

    protected function textMatchesCorrectOption(string $text, Question $question): bool
    {
        $normalized = $this->normalizeLabel($text);

        foreach ($question->options->where('is_correct', true) as $option) {
            if ($this->normalizeLabel((string) $option->label) === $normalized) {
                return true;
            }
            if ($this->normalizeLabel((string) $option->option_text) === $normalized) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeLabel(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    protected function normalizeBoolean(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        $normalized = mb_strtolower(trim((string) $value));

        return match ($normalized) {
            '1', 'true', 't', 'yes', 'y', 'benar', 'b' => true,
            '0', 'false', 'f', 'no', 'n', 'salah', 's' => false,
            default => null,
        };
    }
}
