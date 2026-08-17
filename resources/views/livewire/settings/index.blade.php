<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-ink-900">{{ __('ui.settings.title') }}</h1>
        <p class="mt-1 text-sm text-ink-500">{{ __('ui.settings.subtitle') }}</p>
    </div>

    <section class="max-w-3xl">
        <h2 class="text-sm font-semibold text-ink-900">{{ __('ui.settings.theme.title') }}</h2>
        <p class="mt-1 text-sm text-ink-500">{{ __('ui.settings.theme.subtitle') }}</p>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            @foreach ($themes as $option)
                <button
                    type="button"
                    wire:click="setTheme('{{ $option['id'] }}')"
                    @class([
                        'group relative overflow-hidden rounded-xl border-2 bg-white p-4 text-left transition focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2',
                        'border-brand-600 ring-1 ring-brand-100' => $theme === $option['id'],
                        'border-ink-200 hover:border-ink-300' => $theme !== $option['id'],
                    ])
                >
                    @if ($theme === $option['id'])
                        <span class="absolute right-3 top-3 flex h-5 w-5 items-center justify-center rounded-full bg-brand-600 text-white">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                    @endif

                    {{-- Theme preview swatches --}}
                    @if ($option['id'] === 'default')
                        <div class="mb-3 flex h-16 overflow-hidden rounded-lg border border-ink-200">
                            <div class="w-1/3 bg-ink-900"></div>
                            <div class="flex flex-1 flex-col">
                                <div class="h-1/2 bg-ink-100"></div>
                                <div class="flex h-1/2 items-center gap-1 px-2">
                                    <div class="h-2 w-8 rounded bg-brand-700"></div>
                                    <div class="h-2 flex-1 rounded bg-white border border-ink-200"></div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mb-3 flex h-16 overflow-hidden rounded-lg border border-ink-200">
                            <div class="relative w-1/3 bg-gradient-to-b from-[#0c1929] to-[#1e3a5f]">
                                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-500 via-emerald-500 via-orange-500 to-red-500"></div>
                            </div>
                            <div class="flex flex-1 flex-col bg-[#eef2ff]">
                                <div class="flex h-1/2 items-center gap-1 px-2">
                                    <div class="h-2 w-6 rounded bg-blue-600"></div>
                                    <div class="h-2 w-6 rounded bg-emerald-500"></div>
                                    <div class="h-2 w-6 rounded bg-orange-500"></div>
                                    <div class="h-2 w-6 rounded bg-red-500"></div>
                                </div>
                                <div class="flex h-1/2 items-center px-2">
                                    <div class="h-3 w-full rounded bg-blue-600"></div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="font-medium text-ink-900">{{ $option['label'] }}</div>
                    <p class="mt-1 text-sm text-ink-500">{{ $option['description'] }}</p>
                </button>
            @endforeach
        </div>
    </section>
</div>
