@if (session('success'))
    <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-4 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        {{ session('error') }}
    </div>
@endif

@if (session('status'))
    <div class="mb-4 rounded-md border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-800">
        {{ session('status') }}
    </div>
@endif
