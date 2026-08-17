<?php

namespace App\Services\Term;

use App\Models\User;
use App\Support\AcademicTerm;
use Illuminate\Support\Facades\Auth;

class ActiveTerm
{
    public const SESSION_KEY = 'active_term_code';

    public function current(): string
    {
        $sessionTerm = session(self::SESSION_KEY);

        if (is_string($sessionTerm) && AcademicTerm::isValid($sessionTerm)) {
            return $sessionTerm;
        }

        $user = Auth::user();
        if ($user instanceof User && is_string($user->active_term_code) && AcademicTerm::isValid($user->active_term_code)) {
            session([self::SESSION_KEY => $user->active_term_code]);

            return $user->active_term_code;
        }

        $fallback = AcademicTerm::current();
        session([self::SESSION_KEY => $fallback]);

        return $fallback;
    }

    public function set(string $termCode, ?User $user = null): string
    {
        $termCode = AcademicTerm::assertValid($termCode);
        session([self::SESSION_KEY => $termCode]);

        $user ??= Auth::user();
        if ($user instanceof User) {
            $user->forceFill(['active_term_code' => $termCode])->save();
        }

        return $termCode;
    }

    /**
     * @return list<array{code: string, label: string}>
     */
    public function selectableOptions(): array
    {
        $active = $this->current();

        return collect(AcademicTerm::options($active, before: 4, after: 1))
            ->map(fn (string $code) => [
                'code' => $code,
                'label' => AcademicTerm::label($code),
            ])
            ->all();
    }
}
