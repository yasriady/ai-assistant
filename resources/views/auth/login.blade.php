<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $currentTheme ?? 'default' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('ui.auth.login_title') }}</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            brand: { 50:'#f0fdfa',100:'#ccfbf1',600:'#0d9488',700:'#0f766e',800:'#115e59' },
                            ink: { 50:'#f8fafc',100:'#f1f5f9',200:'#e2e8f0',500:'#64748b',700:'#334155',800:'#1e293b',900:'#0f172a' },
                        },
                        fontFamily: { sans: ['IBM Plex Sans', 'Source Sans 3', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    },
                },
            };
        </script>
    @endif
</head>
<body class="min-h-screen bg-gradient-to-br from-ink-900 via-ink-800 to-brand-900 font-sans text-ink-900 antialiased">
    <div class="absolute right-4 top-4 sm:right-6 sm:top-6">
        <x-locale-switcher class="border-white/20 bg-white/95" />
    </div>

    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-white/10 bg-white shadow-2xl shadow-ink-950/40">
            <div class="border-b border-ink-100 bg-ink-50 px-8 py-7">
                <div class="text-[11px] font-semibold uppercase tracking-[0.2em] text-brand-700">{{ __('ui.academic_platform') }}</div>
                <h1 class="mt-2 text-2xl font-semibold text-ink-900">{{ __('ui.brand') }}</h1>
                <p class="mt-2 text-sm text-ink-500">{{ __('ui.auth.tagline') }}</p>
            </div>

            <div class="space-y-6 px-8 py-7">
                <x-flash />

                @if (config('services.google.client_id'))
                    <a
                        href="{{ route('auth.google') }}"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-800"
                    >
                        {{ __('ui.auth.continue_google') }}
                    </a>

                    <div class="relative text-center text-xs uppercase tracking-wide text-ink-400">
                        <span class="absolute inset-x-0 top-1/2 border-t border-ink-200"></span>
                        <span class="relative bg-white px-3">{{ __('ui.auth.or_demo') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.auth.email') }}</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm shadow-sm outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-100"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-ink-700">{{ __('ui.auth.password') }}</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            class="w-full rounded-lg border border-ink-200 px-3 py-2 text-sm shadow-sm outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-100"
                        >
                    </div>

                    <label class="flex items-center gap-2 text-sm text-ink-600">
                        <input type="checkbox" name="remember" value="1" class="rounded border-ink-300 text-brand-700 focus:ring-brand-600">
                        {{ __('ui.auth.remember') }}
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-lg border border-ink-300 bg-ink-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-ink-800"
                    >
                        {{ __('ui.auth.sign_in') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
