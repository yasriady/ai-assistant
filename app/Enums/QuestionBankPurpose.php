<?php

namespace App\Enums;

enum QuestionBankPurpose: string
{
    case Quiz = 'quiz';
    case Midterm = 'midterm';
    case Final = 'final';
    case Assignment = 'assignment';
    case CaseStudy = 'case_study';
    case General = 'general';

    public function label(): string
    {
        return __('ui.question_bank_purposes.'.$this->value);
    }
}
