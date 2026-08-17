@if ($confirmingDeletion)
    <div
        class="confirm-delete-overlay"
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-delete-title"
        wire:keydown.escape.window="cancelDelete"
    >
        <div class="confirm-delete-backdrop" wire:click="cancelDelete"></div>

        <div class="confirm-delete-panel">
            <h2 id="confirm-delete-title" class="text-lg font-semibold text-ink-900">
                {{ __('ui.delete_dialog.title') }}
            </h2>

            <p class="mt-2 text-sm text-ink-600">
                {{ __('ui.delete_dialog.description') }}
            </p>

            @if ($deletingLabel !== '')
                <p class="mt-3 rounded-md bg-ink-50 px-3 py-2 text-sm font-medium text-ink-800">
                    {{ $deletingLabel }}
                </p>
            @endif

            <label for="delete-confirmation-input" class="mt-4 block text-sm font-medium text-ink-700">
                {{ __('ui.delete_dialog.instruction') }}
            </label>
            <input
                id="delete-confirmation-input"
                type="text"
                wire:model.live="deleteConfirmation"
                autocomplete="off"
                autofocus
                placeholder="delete"
                class="mt-1.5 w-full rounded-md border border-ink-200 px-3 py-2 text-sm outline-none focus:border-rose-600 focus:ring-2 focus:ring-rose-100"
            >

            <div class="mt-6 flex justify-end gap-2">
                <button
                    type="button"
                    wire:click="cancelDelete"
                    class="rounded-md border border-ink-200 px-4 py-2 text-sm text-ink-700 hover:bg-ink-50"
                >
                    {{ __('ui.actions.cancel') }}
                </button>
                <button
                    type="button"
                    wire:click="confirmDelete"
                    @disabled(strtolower(trim($deleteConfirmation)) !== 'delete')
                    class="rounded-md bg-rose-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-rose-800 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-rose-700"
                >
                    {{ __('ui.actions.delete') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Inline fallback so popup works even if an old CSS bundle is cached --}}
    <style>
        .confirm-delete-overlay {
            position: fixed !important;
            inset: 0 !important;
            z-index: 9999 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 1rem !important;
        }
        .confirm-delete-backdrop {
            position: absolute !important;
            inset: 0 !important;
            background: rgba(15, 23, 42, 0.55) !important;
        }
        .confirm-delete-panel {
            position: relative !important;
            z-index: 1 !important;
            width: 100% !important;
            max-width: 28rem !important;
            border-radius: 0.75rem !important;
            border: 1px solid #e2e8f0 !important;
            background: #ffffff !important;
            padding: 1.5rem !important;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35) !important;
        }
    </style>
@endif
