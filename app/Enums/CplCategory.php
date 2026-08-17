<?php

namespace App\Enums;

enum CplCategory: string
{
    case Attitude = 'attitude';
    case GeneralSkill = 'general_skill';
    case Knowledge = 'knowledge';
    case SpecificSkill = 'specific_skill';

    public function label(): string
    {
        return __('ui.cpl.categories.'.$this->value);
    }

    public static function fromCode(string $code): ?self
    {
        $code = strtoupper(trim($code));

        return match (true) {
            str_starts_with($code, 'KK') => self::SpecificSkill,
            str_starts_with($code, 'KU') => self::GeneralSkill,
            str_starts_with($code, 'P') => self::Knowledge,
            str_starts_with($code, 'S') => self::Attitude,
            default => null,
        };
    }
}
