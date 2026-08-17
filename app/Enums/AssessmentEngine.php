<?php

namespace App\Enums;

enum AssessmentEngine: string
{
    case Document = 'document';
    case Exam = 'exam';
    case Project = 'project';

    public function label(): string
    {
        return __('ui.engines.'.$this->value);
    }
}
