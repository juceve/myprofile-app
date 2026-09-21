@props(['type' => 'info'])
@php
    $styles = [
        'success' => 'border-emerald-300 bg-emerald-600 text-white shadow-emerald-950/25 dark:border-emerald-400 dark:bg-emerald-500 dark:text-emerald-950 dark:shadow-black/40',
        'danger' => 'border-red-300 bg-red-600 text-white shadow-red-950/25 dark:border-red-400 dark:bg-red-500 dark:text-red-950 dark:shadow-black/40',
        'info' => 'border-sky-300 bg-sky-600 text-white shadow-sky-950/25 dark:border-sky-400 dark:bg-sky-500 dark:text-sky-950 dark:shadow-black/40',
    ];
    $icons = ['success' => 'fa-circle-check', 'danger' => 'fa-circle-exclamation', 'info' => 'fa-circle-info'];
@endphp
<div role="alert" aria-live="polite" data-toast class="toast-enter pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border-l-4 px-4 py-3.5 text-sm font-semibold shadow-xl ring-1 ring-black/10 transition duration-300 dark:ring-white/10 {{ $styles[$type] ?? $styles['info'] }}" {{ $attributes }}>
    <i class="fa-solid {{ $icons[$type] ?? $icons['info'] }} mt-0.5 text-base" aria-hidden="true"></i>
    <span class="flex-1 leading-5">{{ $slot }}</span>
    <button type="button" data-toast-close class="-mr-1 -mt-1 rounded-lg p-1 text-current/80 transition hover:bg-black/10 hover:text-current focus:outline-none focus:ring-2 focus:ring-current/50 dark:hover:bg-white/15" aria-label="Cerrar notificación"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
</div>
