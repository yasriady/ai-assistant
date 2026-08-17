<?php

namespace App\Enums;

enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case TrueFalse = 'true_false';
    case ShortAnswer = 'short_answer';
    case Essay = 'essay';
    case Calculation = 'calculation';
    case CaseStudy = 'case_study';
    case Programming = 'programming';
    case Diagram = 'diagram';
    case Mixed = 'mixed';

    public function label(): string
    {
        return __('ui.question_types.'.$this->value);
    }
}
