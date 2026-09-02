@props(['type' => 'info'])
@php($styles = ['success' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300', 'danger' => 'border-red-200 bg-red-50 text-red-800 dark:border-red-900 dark:bg-red-950/50 dark:text-red-300', 'info' => 'border-sky-200 bg-sky-50 text-sky-800 dark:border-sky-900 dark:bg-sky-950/50 dark:text-sky-300'])
<div role="alert" data-toast class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border px-4 py-3 text-sm font-medium shadow-lg transition duration-300 {{ $styles[$type] ?? $styles['info'] }}" {{ $attributes }}>
    <span class="flex-1">{{ $slot }}</span>
    <button type="button" data-toast-close class="-mr-1 -mt-1 rounded-lg p-1 text-current/60 transition hover:bg-black/5 hover:text-current dark:hover:bg-white/10" aria-label="Cerrar notificación">✕</button>
</div>
