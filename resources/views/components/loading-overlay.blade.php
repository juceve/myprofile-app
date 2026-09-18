@props([
    'message' => 'Procesando solicitud...',
    'id' => 'app-loading-overlay',
])

<div
    id="{{ $id }}"
    data-loading-overlay
    data-default-message="{{ $message }}"
    role="status"
    aria-live="polite"
    aria-busy="false"
    aria-hidden="true"
    {{ $attributes->merge(['class' => 'fixed inset-0 z-[200] hidden items-center justify-center bg-slate-950/55 px-6 backdrop-blur-[2px]']) }}
>
    <div class="flex w-full max-w-xs flex-col items-center gap-4 rounded-2xl border border-white/20 bg-white px-6 py-5 text-center shadow-2xl dark:border-slate-700 dark:bg-slate-900">
        <span class="flex h-12 w-12 items-center justify-center rounded-full border-4 border-emerald-100 border-t-emerald-600 dark:border-emerald-950 dark:border-t-emerald-400" aria-hidden="true">
            <span class="h-5 w-5 animate-spin rounded-full border-2 border-slate-200 border-t-emerald-600 dark:border-slate-700 dark:border-t-emerald-400"></span>
        </span>
        <span data-loading-message class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $message }}</span>
    </div>
</div>
