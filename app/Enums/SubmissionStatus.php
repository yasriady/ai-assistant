<?php

namespace App\Enums;

enum SubmissionStatus: string
{
    case Uploaded = 'uploaded';
    case Processing = 'processing';
    case Assessed = 'assessed';
    case Reviewed = 'reviewed';
    case Finalized = 'finalized';
    case Failed = 'failed';

    public function label(): string
    {
        return __('ui.status.'.$this->value);
    }
}
