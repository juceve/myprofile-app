<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>AdminCorp Verde · Gestión institucional</title>
        <script>
            (() => {
                const theme = localStorage.getItem('admin_panel_theme') || 'light';
                const dark = theme === 'dark' || (theme === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', dark);
            })();
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <header class="border-b border-slate-200/80 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-950/90">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-6 py-4 lg:px-8">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 font-bold text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300">A</span>
                    <span><strong class="block text-sm tracking-tight">AdminCorp <span class="text-emerald-600 dark:text-emerald-400">Verde</span></strong><small class="block text-[10px] font-medium uppercase tracking-widest text-slate-400">Panel corporativo</small></span>
                </a>
                <nav class="hidden items-center gap-6 text-sm font-semibold text-slate-500 dark:text-slate-400 md:flex">
                    <a href="#soluciones" class="transition hover:text-emerald-600 dark:hover:text-emerald-400">Soluciones</a>
                    <a href="#indicadores" class="transition hover:text-emerald-600 dark:hover:text-emerald-400">Indicadores</a>
                    <a href="#seguridad" class="transition hover:text-emerald-600 dark:hover:text-emerald-400">Seguridad</a>
                </nav>
                <div class="flex items-center gap-2">
                    <button data-theme-toggle class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-emerald-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">☼ <span class="hidden sm:inline">Tema</span></button>
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700">Ir al panel</a>
                    @else
                        <a href="{{ route('login') }}" class="hidden rounded-xl px-3 py-2.5 text-xs font-bold text-slate-600 transition hover:text-emerald-600 dark:text-slate-300 sm:inline-flex">Iniciar sesión</a>
                        <a href="{{ route('register') }}" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700">Crear cuenta</a>
                    @endauth
                </div>
            </div>
        </header>

        <main>
            <section class="relative overflow-hidden">
                <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full bg-emerald-200/50 blur-3xl dark:bg-emerald-950/50"></div>
                <div class="absolute -bottom-48 -left-32 h-96 w-96 rounded-full bg-sky-100 blur-3xl dark:bg-sky-950/30"></div>
                <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-6 py-20 lg:grid-cols-2 lg:px-8 lg:py-28">
                    <div>
                        <x-badge>✓ Sistema integrado de gestión institucional</x-badge>
                        <h1 class="mt-6 max-w-2xl text-4xl font-bold tracking-tight sm:text-6xl">Decisiones claras para una gestión <span class="text-emerald-600 dark:text-emerald-400">más eficiente.</span></h1>
                        <p class="mt-6 max-w-xl text-base leading-8 text-slate-600 dark:text-slate-300">Centraliza trámites, presupuestos y auditorías en un solo espacio. AdminCorp Verde convierte la operación institucional en información accionable.</p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            @auth
                                <x-button href="{{ route('dashboard') }}">Abrir panel de control</x-button>
                            @else
                                <x-button href="{{ route('register') }}">Comenzar ahora →</x-button>
                                <x-button href="{{ route('login') }}" variant="secondary">Ya tengo una cuenta</x-button>
                            @endauth
                        </div>
                        <div class="mt-10 flex flex-wrap gap-6 text-xs font-semibold text-slate-500 dark:text-slate-400">
                            <span>✓ Datos centralizados</span><span>✓ Seguimiento en tiempo real</span><span>✓ Auditoría trazable</span>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="rounded-3xl border border-slate-200/80 bg-white p-4 shadow-2xl shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/30 sm:p-6">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800"><div><p class="text-[10px] font-bold uppercase tracking-widest text-emerald-600">Vista general</p><h2 class="mt-1 font-bold dark:text-white">Panel de Control</h2></div><span class="rounded-lg bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">En línea</span></div>
                            <div class="mt-5 grid grid-cols-2 gap-3"><x-card class="p-4"><p class="text-[10px] font-bold uppercase text-slate-400">Presupuesto ejecutado</p><p class="mt-2 text-xl font-bold dark:text-white">$91,400</p><p class="mt-1 text-[10px] font-semibold text-emerald-600">↗ 12.5%</p></x-card><x-card class="p-4"><p class="text-[10px] font-bold uppercase text-slate-400">Trámites atendidos</p><p class="mt-2 text-xl font-bold dark:text-white">675</p><p class="mt-1 text-[10px] font-semibold text-sky-600">▣ 8.4%</p></x-card></div>
                            <div class="mt-3 rounded-2xl border border-slate-100 p-4 dark:border-slate-800"><div class="flex items-center justify-between"><p class="text-xs font-bold dark:text-white">Actividad institucional</p><span class="text-[10px] text-slate-400">2026</span></div><div class="mt-5 flex h-28 items-end gap-2">@foreach([35,48,42,65,55,78,68,92,82] as $height)<div class="flex-1 rounded-t-md bg-emerald-500/80" style="height: {{ $height }}%"></div>@endforeach</div></div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="indicadores" class="border-y border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-900/50">
                <div class="mx-auto grid max-w-7xl gap-8 px-6 py-10 sm:grid-cols-3 lg:px-8"><div><p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">98.6%</p><p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">Cumplimiento normativo</p></div><div><p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">2.4 días</p><p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">Tiempo promedio de respuesta</p></div><div><p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">24/7</p><p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">Información disponible</p></div></div>
            </section>

            <section id="soluciones" class="mx-auto max-w-7xl px-6 py-20 lg:px-8">
                <div class="max-w-2xl"><p class="text-xs font-bold uppercase tracking-widest text-emerald-600">Una operación conectada</p><h2 class="mt-3 text-3xl font-bold tracking-tight dark:text-white">Todo lo que necesitas para avanzar</h2><p class="mt-3 text-sm leading-7 text-slate-500 dark:text-slate-400">Herramientas simples para equipos que necesitan control, colaboración y resultados medibles.</p></div>
                <div class="mt-10 grid gap-5 md:grid-cols-3"><x-card class="p-6"><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-xl text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">▦</span><h3 class="mt-5 font-bold dark:text-white">Panel ejecutivo</h3><p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">Indicadores esenciales para entender el estado de tu institución de un vistazo.</p></x-card><x-card class="p-6"><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 text-xl text-sky-600 dark:bg-sky-950/50 dark:text-sky-400">▤</span><h3 class="mt-5 font-bold dark:text-white">Flujos trazables</h3><p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">Sigue cada expediente, responsable y plazo con una vista clara y ordenada.</p></x-card><x-card class="p-6"><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-xl text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">✓</span><h3 class="mt-5 font-bold dark:text-white">Cumplimiento</h3><p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">Conserva el historial de actividad y fortalece la rendición de cuentas.</p></x-card></div>
            </section>

            <section id="seguridad" class="bg-emerald-950 text-white">
                <div class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-8 px-6 py-14 sm:flex-row sm:items-center lg:px-8"><div><p class="text-xs font-bold uppercase tracking-widest text-emerald-300">Listo para crecer</p><h2 class="mt-3 text-2xl font-bold">Una base segura para tu próxima decisión.</h2><p class="mt-2 max-w-xl text-sm leading-6 text-emerald-100/75">AdminCorp Verde combina una experiencia moderna con controles y permisos preparados para equipos institucionales.</p></div><a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="shrink-0 rounded-xl bg-white px-5 py-3 text-sm font-bold text-emerald-950 transition hover:bg-emerald-50">{{ auth()->check() ? 'Ver mi panel' : 'Crear mi cuenta' }} →</a></div>
            </section>
        </main>

        <footer class="border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-6 py-6 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between lg:px-8"><span>© {{ date('Y') }} AdminCorp Verde</span><span>Laravel 13 · Gestión institucional</span></div>
        </footer>
    </body>
</html>
