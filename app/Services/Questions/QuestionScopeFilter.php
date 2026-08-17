<?php

namespace App\Services\Questions;

use App\Enums\AssessmentType;
use App\Enums\QuestionScopeType;
use App\Models\Assessment;
use App\Models\Course;
use Illuminate\Database\Eloquent\Builder;

class QuestionScopeFilter
{
    public function apply(Builder $query, Assessment $assessment): Builder
    {
        $course = $assessment->course;

        if (! $course) {
            return $query->whereRaw('1 = 0');
        }

        $query->where('course_id', $course->id);

        return match ($assessment->type) {
            AssessmentType::MidtermExam => $this->forMidterm($query, $course),
            AssessmentType::FinalExam,
            AssessmentType::EssayExam,
            AssessmentType::MixedExam => $this->forFinal($query, $course),
            AssessmentType::Quiz => $this->forQuiz($query, $course),
            AssessmentType::Assignment => $this->forAssignment($query),
            default => $query,
        };
    }

    private function forMidterm(Builder $query, Course $course): Builder
    {
        return $query->where(function (Builder $inner) use ($course): void {
            $inner->where('scope_type', QuestionScopeType::General->value)
                ->orWhere(function (Builder $specific) use ($course): void {
                    $specific->where('scope_type', QuestionScopeType::Specific->value)
                        ->where(function (Builder $topics) use ($course): void {
                            $topics->whereHas('courseTopics', fn (Builder $q) => $q->where('week_number', '<=', $course->midterm_week))
                                ->orWhereDoesntHave('courseTopics');
                        });
                });
        });
    }

    private function forFinal(Builder $query, Course $course): Builder
    {
        return $query->whereIn('scope_type', [
            QuestionScopeType::Specific->value,
            QuestionScopeType::General->value,
            QuestionScopeType::CaseStudy->value,
        ]);
    }

    private function forQuiz(Builder $query, Course $course): Builder
    {
        return $query->whereIn('scope_type', [
            QuestionScopeType::Specific->value,
            QuestionScopeType::General->value,
        ]);
    }

    private function forAssignment(Builder $query): Builder
    {
        return $query->whereIn('scope_type', [
            QuestionScopeType::General->value,
            QuestionScopeType::CaseStudy->value,
        ]);
    }
}
