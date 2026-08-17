<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $currentTheme ?? 'default' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('ui.brand') }}</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            brand: {
                                50: '#f0fdfa', 100: '#ccfbf1', 200: '#99f6e4', 300: '#5eead4',
                                400: '#2dd4bf', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e',
                                800: '#115e59', 900: '#134e4a', 950: '#042f2e',
                            },
                            ink: {
                                50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1',
                                400: '#94a3b8', 500: '#64748b', 600: '#475569', 700: '#334155',
                                800: '#1e293b', 900: '#0f172a', 950: '#020617',
                            },
                        },
                        fontFamily: {
                            sans: ['IBM Plex Sans', 'Source Sans 3', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        },
                    },
                },
            };
        </script>
        <style>
            :root, [data-theme='default'] {
                --app-bg: #f1f5f9;
                --app-surface: #ffffff;
                --app-border: #e2e8f0;
                --app-sidebar: #0f172a;
                --app-accent: #0f766e;
                --color-brand-700: #0f766e;
            }
            [data-theme='vivid'] {
                --app-bg: #eef2ff;
                --color-brand-700: #1d4ed8;
                --color-brand-600: #2563eb;
                --color-brand-800: #1e40af;
                --color-brand-300: #93c5fd;
                --color-ink-100: #eef2ff;
                --color-ink-200: #c7d2fe;
            }
            [data-theme='vivid'] .theme-sidebar {
                background: linear-gradient(180deg, #0c1929, #122d4d) !important;
            }
            [data-theme='vivid'] .theme-sidebar-accent {
                height: 3px;
                background: linear-gradient(90deg, #2563eb, #10b981, #f97316, #ef4444);
            }
            [data-theme='vivid'] .theme-body { background-color: #eef2ff !important; }
            body, .theme-body { font-family: 'IBM Plex Sans', 'Source Sans 3', ui-sans-serif, system-ui, sans-serif; background: var(--app-bg); }
        </style>
    @endif

    @livewireStyles
    <script>
        (function () {
            var theme = @json($currentTheme ?? 'default');
            document.documentElement.dataset.theme = theme;
        })();
    </script>
</head>
<body class="theme-body min-h-screen antialiased">
    <div class="flex min-h-screen">
        <aside class="theme-sidebar hidden w-64 shrink-0 flex-col bg-ink-900 text-ink-200 lg:flex">
            <div class="theme-sidebar-accent"></div>
            <div class="border-b border-ink-800 px-5 py-6">
                <a href="{{ route('dashboard') }}" class="block">
                    <div class="theme-sidebar-brand text-[11px] font-semibold uppercase tracking-[0.18em] text-brand-300">{{ __('ui.platform') }}</div>
                    <div class="mt-1 text-lg font-semibold leading-snug text-white">{{ __('ui.brand') }}</div>
                </a>
            </div>

            <nav class="flex-1 space-y-1 px-3 py-4 text-sm">
                @php
                    $nav = [
                        ['label' => __('ui.nav.dashboard'), 'route' => 'dashboard', 'match' => 'dashboard'],
                        ['label' => __('ui.nav.courses'), 'route' => 'courses.index', 'match' => 'courses.*'],
                        ['label' => __('ui.nav.cpl'), 'route' => 'cpls.index', 'match' => 'cpls.*'],
                        ['label' => __('ui.nav.students'), 'route' => 'students.index', 'match' => 'students.*'],
                        ['label' => __('ui.nav.rubrics'), 'route' => 'rubrics.index', 'match' => 'rubrics.*'],
                        ['label' => __('ui.nav.assessments'), 'route' => 'assessments.index', 'match' => 'assessments.*'],
                        ['label' => __('ui.nav.question_bank'), 'route' => 'question-banks.index', 'match' => 'question-banks.*'],
                    ];
                @endphp

                @foreach ($nav as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        @class([
                            'block rounded-md px-3 py-2 transition',
                            'theme-nav-active bg-brand-700 text-white' => request()->routeIs($item['match']),
                            'text-ink-300 hover:bg-ink-800 hover:text-white' => ! request()->routeIs($item['match']),
                        ])
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach

                <a
                    href="{{ route('settings') }}"
                    @class([
                        'mt-4 block rounded-md px-3 py-2 transition',
                        'theme-nav-active bg-brand-700 text-white' => request()->routeIs('settings'),
                        'text-ink-300 hover:bg-ink-800 hover:text-white' => ! request()->routeIs('settings'),
                    ])
                >
                    {{ __('ui.nav.settings') }}
                </a>

                @if (auth()->user()?->isAdmin())
                    <a
                        href="{{ route('admin.ai-settings') }}"
                        @class([
                            'block rounded-md px-3 py-2 transition',
                            'theme-nav-active bg-brand-700 text-white' => request()->routeIs('admin.ai-settings'),
                            'text-ink-300 hover:bg-ink-800 hover:text-white' => ! request()->routeIs('admin.ai-settings'),
                        ])
                    >
                        {{ __('ui.nav.ai_settings') }}
                    </a>
                @endif
            </nav>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="theme-header sticky top-0 z-20 border-b border-ink-200 bg-white/95 backdrop-blur">
                <div class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                    <div class="min-w-0 lg:hidden">
                        <div class="truncate text-sm font-semibold text-ink-900">{{ __('ui.brand') }}</div>
                    </div>
                    <div class="hidden min-w-0 items-center gap-3 lg:flex">
                        <span class="text-sm text-ink-500">{{ $header ?? __('ui.workspace') }}</span>
                        <span class="text-ink-300">·</span>
                        <x-term-switcher />
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="lg:hidden">
                            <x-term-switcher />
                        </div>
                        <x-locale-switcher />
                        <div class="text-right">
                            <div class="text-sm font-medium text-ink-900">{{ auth()->user()?->name }}</div>
                            <div class="text-xs text-ink-500">{{ auth()->user()?->role?->label() }}</div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-md border border-ink-200 px-3 py-1.5 text-sm text-ink-700 transition hover:border-ink-300 hover:bg-ink-50">
                                {{ __('ui.auth.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <x-flash />
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
