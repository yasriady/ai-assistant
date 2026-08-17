<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! config('services.google.client_id')) {
            return redirect()
                ->route('login')
                ->with('error', 'Google OAuth is not configured. Use email/password demo login.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('login')
                ->with('error', 'Google authentication failed. Please try again.');
        }

        $email = strtolower((string) $googleUser->getEmail());
        $googleId = (string) $googleUser->getId();

        $user = User::query()
            ->where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            $user->fill([
                'google_id' => $user->google_id ?: $googleId,
                'name' => $googleUser->getName() ?: $user->name,
                'avatar' => $googleUser->getAvatar() ?: $user->avatar,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } else {
            $user = User::query()->create([
                'name' => $googleUser->getName() ?: $email,
                'email' => $email,
                'google_id' => $googleId,
                'avatar' => $googleUser->getAvatar(),
                'role' => $this->resolveRole($email),
                'email_verified_at' => now(),
                'password' => null,
            ]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }

    private function resolveRole(string $email): UserRole
    {
        $adminEmails = collect(explode(',', (string) env('ADMIN_EMAILS', '')))
            ->map(fn (string $value) => strtolower(trim($value)))
            ->filter()
            ->all();

        if (User::query()->count() === 0 || in_array($email, $adminEmails, true)) {
            return UserRole::Admin;
        }

        return UserRole::Lecturer;
    }
}
