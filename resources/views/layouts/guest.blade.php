<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[radial-gradient(circle_at_top_right,_rgba(16,185,129,.16),_transparent_42%)] px-4 py-10 dark:bg-[radial-gradient(circle_at_top_right,_rgba(16,185,129,.22),_transparent_42%)]">
            <button data-theme-toggle class="absolute right-4 top-4 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-emerald-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300" title="Cambiar tema">
                ☼ <span class="hidden sm:inline">Cambiar tema</span>
            </button>
            <div class="w-full max-w-md">
                <a href="/" wire:navigate class="mb-6 flex items-center justify-center gap-3 text-slate-900 dark:text-white">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 font-bold text-emerald-700 dark:border-emerald-400/40 dark:bg-emerald-500/10 dark:text-emerald-300">A</span>
                    <span><strong class="block tracking-tight">AdminCorp <span class="text-emerald-600 dark:text-emerald-400">Verde</span></strong><small class="block text-[10px] uppercase tracking-widest text-slate-400">Panel corporativo</small></span>
                </a>
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                {{ $slot }}
            </div>
            </div>
        </div>
    </body>
</html>
