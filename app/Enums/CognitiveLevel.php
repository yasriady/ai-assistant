<?php

namespace App\Enums;

enum CognitiveLevel: string
{
    case C1 = 'C1';
    case C2 = 'C2';
    case C3 = 'C3';
    case C4 = 'C4';
    case C5 = 'C5';
    case C6 = 'C6';

    public function label(): string
    {
        return __('ui.cognitive.'.$this->value);
    }
}
