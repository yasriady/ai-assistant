<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Lecturer = 'lecturer';

    public function label(): string
    {
        return __('ui.roles.'.$this->value);
    }
}
