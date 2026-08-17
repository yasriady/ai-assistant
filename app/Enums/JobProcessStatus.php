<?php

namespace App\Enums;

enum JobProcessStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __('ui.status.'.$this->value);
    }
}
