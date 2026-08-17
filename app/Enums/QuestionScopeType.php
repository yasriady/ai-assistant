<?php

namespace App\Enums;

enum QuestionScopeType: string
{
    case Specific = 'specific';
    case General = 'general';
    case CaseStudy = 'case_study';

    public function label(): string
    {
        return __('ui.question_scope_types.'.$this->value);
    }
}
