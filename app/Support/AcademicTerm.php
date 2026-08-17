<?php

namespace App\Support;

use Carbon\CarbonInterface;
use InvalidArgumentException;

class AcademicTerm
{
    /**
     * Indonesian academic term code: YYYY + 1 (ganjil) or 2 (genap).
     * Examples: 20251, 20252, 20261.
     */
    public static function isValid(string $code): bool
    {
        return (bool) preg_match('/^\d{4}[12]$/', $code);
    }

    public static function assertValid(string $code): string
    {
        if (! self::isValid($code)) {
            throw new InvalidArgumentException("Invalid academic term code [{$code}].");
        }

        return $code;
    }

    public static function year(string $code): int
    {
        self::assertValid($code);

        return (int) substr($code, 0, 4);
    }

    public static function season(string $code): string
    {
        self::assertValid($code);

        return substr($code, 4, 1) === '1' ? 'odd' : 'even';
    }

    public static function isOdd(string $code): bool
    {
        return self::season($code) === 'odd';
    }

    /**
     * Display label, e.g. "20251 — Ganjil 2025/2026".
     */
    public static function label(string $code, ?string $locale = null): string
    {
        self::assertValid($code);

        $year = self::year($code);
        $academicYear = $year.'/'.($year + 1);
        $seasonKey = self::isOdd($code) ? 'ui.term.odd' : 'ui.term.even';
        $season = __($seasonKey, [], $locale);

        return "{$code} — {$season} {$academicYear}";
    }

    public static function shortLabel(string $code, ?string $locale = null): string
    {
        self::assertValid($code);

        $year = self::year($code);
        $seasonKey = self::isOdd($code) ? 'ui.term.odd' : 'ui.term.even';

        return $code.' ('.__($seasonKey, [], $locale).' '.$year.'/'.($year + 1).')';
    }

    public static function semesterName(string $code, ?string $locale = null): string
    {
        return __(self::isOdd($code) ? 'ui.term.odd' : 'ui.term.even', [], $locale);
    }

    public static function academicYear(string $code): string
    {
        $year = self::year($code);

        return $year.'/'.($year + 1);
    }

    /**
     * Current term based on Indonesian academic calendar:
     * - Aug–Jan → ganjil (…1)
     * - Feb–Jul → genap (…2)
     */
    public static function current(?CarbonInterface $date = null): string
    {
        $date ??= now();
        $month = (int) $date->month;
        $year = (int) $date->year;

        if ($month >= 8) {
            return $year.'1';
        }

        if ($month === 1) {
            return ($year - 1).'1';
        }

        return ($year - 1).'2';
    }

    /**
     * @return list<string>
     */
    public static function options(?string $around = null, int $before = 3, int $after = 1): array
    {
        $around ??= self::current();
        self::assertValid($around);

        $codes = [];
        $cursor = $around;

        for ($i = 0; $i < $before; $i++) {
            $cursor = self::previous($cursor);
            array_unshift($codes, $cursor);
        }

        $codes[] = $around;
        $cursor = $around;

        for ($i = 0; $i < $after; $i++) {
            $cursor = self::next($cursor);
            $codes[] = $cursor;
        }

        return $codes;
    }

    public static function previous(string $code): string
    {
        self::assertValid($code);

        if (self::isOdd($code)) {
            return (self::year($code) - 1).'2';
        }

        return self::year($code).'1';
    }

    public static function next(string $code): string
    {
        self::assertValid($code);

        if (self::isOdd($code)) {
            return self::year($code).'2';
        }

        return (self::year($code) + 1).'1';
    }
}
