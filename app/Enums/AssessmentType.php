<?php

namespace App\Enums;

enum AssessmentType: string
{
    case Assignment = 'assignment';
    case PracticalReport = 'practical_report';
    case Journal = 'journal';
    case Paper = 'paper';
    case Project = 'project';
    case Quiz = 'quiz';
    case MidtermExam = 'midterm_exam';
    case FinalExam = 'final_exam';
    case EssayExam = 'essay_exam';
    case MixedExam = 'mixed_exam';

    public function label(): string
    {
        return __('ui.assessment_types.'.$this->value);
    }

    public function engine(): AssessmentEngine
    {
        return match ($this) {
            self::Assignment,
            self::PracticalReport,
            self::Journal,
            self::Paper => AssessmentEngine::Document,
            self::Project => AssessmentEngine::Project,
            self::Quiz,
            self::MidtermExam,
            self::FinalExam,
            self::EssayExam,
            self::MixedExam => AssessmentEngine::Exam,
        };
    }
}
