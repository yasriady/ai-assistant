<?php

namespace App\Services\Theme;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserTheme
{
    public const SESSION_KEY = 'theme';

    /** @var list<string> */
    public const SUPPORTED = ['default', 'vivid'];

    public function current(): string
    {
        $sessionTheme = session(self::SESSION_KEY);

        if (is_string($sessionTheme) && $this->isValid($sessionTheme)) {
            return $sessionTheme;
        }

        $user = Auth::user();
        if ($user instanceof User && is_string($user->theme) && $this->isValid($user->theme)) {
            session([self::SESSION_KEY => $user->theme]);

            return $user->theme;
        }

        session([self::SESSION_KEY => 'default']);

        return 'default';
    }

    public function set(string $theme, ?User $user = null): string
    {
        $theme = $this->assertValid($theme);
        session([self::SESSION_KEY => $theme]);

        $user ??= Auth::user();
        if ($user instanceof User) {
            $user->forceFill(['theme' => $theme])->save();
        }

        return $theme;
    }

    public function isValid(string $theme): bool
    {
        return in_array($theme, self::SUPPORTED, true);
    }

    public function assertValid(string $theme): string
    {
        if (! $this->isValid($theme)) {
            abort(404);
        }

        return $theme;
    }

    /**
     * @return list<array{id: string, label: string, description: string}>
     */
    public function options(): array
    {
        return [
            [
                'id' => 'default',
                'label' => __('ui.settings.theme.default'),
                'description' => __('ui.settings.theme.default_desc'),
            ],
            [
                'id' => 'vivid',
                'label' => __('ui.settings.theme.vivid'),
                'description' => __('ui.settings.theme.vivid_desc'),
            ],
        ];
    }
}
