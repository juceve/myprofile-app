<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Panel de Control' }} · AdminCorp Verde</title>
    <script>
        (() => {
            const theme = localStorage.getItem('admin_panel_theme') || 'light';
            const dark = theme === 'dark' || (theme === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    <div class="min-h-full">
        <div id="sidebar-backdrop" class="fixed inset-0 z-40 hidden bg-slate-950/30 lg:hidden"></div>
        <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 dark:border-slate-800 dark:bg-slate-900 lg:translate-x-0">
            <div class="flex h-16 items-center justify-between border-b border-slate-100 px-5 dark:border-slate-800">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 font-bold text-emerald-700">A</span>
                    <span><strong class="block text-sm tracking-tight">AdminCorp <span class="text-emerald-600">Verde</span></strong><small class="block text-[10px] font-medium uppercase tracking-widest text-slate-400">Panel corporativo</small></span>
                </a>
                <button data-sidebar-close class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 lg:hidden" aria-label="Cerrar menú">✕</button>
            </div>
            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
                @php
                    $canAccess = fn (array $item): bool => empty($item['can']) || (auth()->check() && auth()->user()->can($item['can']));
                    $iconClass = fn (?string $icon): string => ! $icon ? 'fa-solid fa-circle' : (str_contains($icon, ' ') ? $icon : "fa-solid fa-{$icon}");
                @endphp
                @foreach(config('sidebar', []) as $section)
                    @php
                        $items = collect($section['items'] ?? [])
                            ->map(function (array $item) use ($canAccess) {
                                if (! empty($item['submenu'])) {
                                    $item['submenu'] = collect($item['submenu'])->filter($canAccess)->values()->all();
                                }

                                return $item;
                            })
                            ->filter(fn (array $item) => (empty($item['submenu']) && $canAccess($item)) || (! empty($item['submenu']) && count($item['submenu']) > 0))
                            ->values();
                    @endphp
                    <div>
                        <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ $section['title'] }}</p>
                        <div class="space-y-1">
                            @foreach($items as $item)
                                @php
                                    $routeName = $item['route'] ?? null;
                                    $url = $item['url'] ?? ($routeName ? route($routeName === 'dashboard' ? 'dashboard' : $routeName) : '#');
                                    $children = $item['submenu'] ?? [];
                                    $isActive = ($routeName && request()->routeIs($routeName === 'dashboard' ? 'dashboard*' : $routeName))
                                        || (! $routeName && request()->is(trim(parse_url($url, PHP_URL_PATH), '/')));
                                    $hasActiveChild = collect($children)->contains(function (array $child): bool {
                                        if (! empty($child['route'])) {
                                            return request()->routeIs($child['route']);
                                        }

                                        return ! empty($child['url']) && request()->is(trim(parse_url($child['url'], PHP_URL_PATH), '/'));
                                    });
                                @endphp
                                @if($children)
                                    <div data-sidebar-group>
                                        <button type="button" data-sidebar-group-toggle aria-expanded="{{ ($hasActiveChild ? 'true' : 'false') }}" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-xs font-semibold {{ $hasActiveChild ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                                            <i class="{{ $iconClass($item['icon'] ?? null) }} w-5 text-center text-emerald-600" aria-hidden="true"></i><span class="flex-1">{{ $item['name'] }}</span><i data-sidebar-chevron class="fa-solid fa-chevron-down text-slate-400 transition-transform {{ $hasActiveChild ? 'rotate-180' : '' }}" aria-hidden="true"></i>
                                        </button>
                                        <div data-sidebar-submenu class="{{ $hasActiveChild ? '' : 'hidden' }} ml-5 space-y-1 border-l border-slate-200 pl-3 dark:border-slate-700">
                                            @foreach($children as $child)
                                                @php
                                                    $childUrl = ! empty($child['route']) ? route($child['route']) : ($child['url'] ?? '#');
                                                    $childActive = ! empty($child['route'])
                                                        ? request()->routeIs($child['route'])
                                                        : request()->is(trim(parse_url($childUrl, PHP_URL_PATH), '/'));
                                                @endphp
                                                <a href="{{ $childUrl }}" class="block rounded-lg px-3 py-2 text-xs font-semibold {{ $childActive ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">{{ $child['name'] }}</a>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ $url }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold {{ $isActive ? 'border border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800' }}">
                                        <i class="{{ $iconClass($item['icon'] ?? null) }} w-5 text-center text-emerald-600" aria-hidden="true"></i><span>{{ $item['name'] }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
            <div class="m-3 rounded-2xl bg-emerald-900 p-4 text-white">
                <p class="text-xs font-bold">Sistema integrado</p>
                <p class="mt-1 text-[11px] leading-relaxed text-emerald-100">Laravel + Blade + Tailwind listos para crecer.</p>
            </div>
        </aside>

        <div class="lg:pl-72">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between gap-4 border-b border-slate-200 bg-white/95 px-4 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <button data-sidebar-open class="rounded-xl p-2 text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 lg:hidden" aria-label="Abrir menú">☰</button>
                    <div>
                        <p class="text-xs text-slate-500"><span class="font-semibold text-emerald-600">{{ $eyebrow ?? 'Principal' }}</span> / {{ $title ?? 'Panel de Control' }}</p>
                        <h1 class="truncate text-base font-bold tracking-tight dark:text-white">{{ $title ?? 'Panel de Control' }}</h1>
                    </div>
                </div>
                <form action="{{ route('dashboard') }}" class="hidden max-w-md flex-1 md:block">
                    <input name="q" value="{{ request('q') }}" class="w-full rounded-xl border border-transparent bg-slate-100 px-4 py-2 text-sm text-slate-900 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:bg-slate-800" placeholder="Buscar trámites, expedientes, usuarios...">
                </form>
                <div class="flex items-center gap-2">
                    @can('empresas.view')
                        <form method="POST" action="{{ route('empresa-mandante.seleccionar') }}" class="flex min-w-0 items-center gap-2">
                            @csrf
                            <label for="empresa-mandante-activa" class="sr-only">Empresa mandante activa</label>
                            <select id="empresa-mandante-activa" name="empresa_mandante_id" onchange="this.form.submit()" class="max-w-[10rem] truncate rounded-xl border border-emerald-200 bg-emerald-50 px-2 py-2 text-xs font-bold text-emerald-800 outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/10 sm:max-w-[15rem] sm:px-3 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">
                                <option value="">Seleccione empresa</option>
                                @foreach($empresasMandantesActivas ?? [] as $empresaMandante)
                                    <option value="{{ $empresaMandante->id }}" @selected(($empresaMandanteActiva?->id ?? null) === $empresaMandante->id)>{{ $empresaMandante->codigo }} · {{ $empresaMandante->razon_social }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endcan
                    <a href="{{ route('blank') }}" class="hidden items-center gap-2 rounded-xl bg-emerald-600 px-3 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700 sm:inline-flex">＋ Nuevo Trámite</a>
                    <button data-theme-toggle class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700" title="Cambiar tema">☼ <span class="hidden md:inline">Modo Claro</span></button>
                    <button class="relative rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800" title="Notificaciones">♧<span class="absolute right-1 top-1 h-2 w-2 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-900"></span></button>
                    <div class="relative hidden border-l border-slate-200 pl-3 dark:border-slate-700 sm:block">
                        <button data-profile-toggle class="flex items-center gap-2 rounded-xl px-2 py-1.5 text-left hover:bg-slate-100 dark:hover:bg-slate-800" aria-expanded="false" aria-controls="profile-menu">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">{{ str(auth()->user()?->name ?? 'Carlos Mendoza')->explode(' ')->map(fn ($name) => str($name)->substr(0, 1))->take(2)->implode('') }}</span>
                            <span><strong class="block text-xs font-semibold">{{ auth()->user()?->name ?? 'Carlos Mendoza' }}</strong><small class="block text-[10px] text-slate-400">{{ auth()->user()?->email ?? 'Administrador' }}</small></span><span class="text-slate-400">⌄</span>
                        </button>
                        <div id="profile-menu" class="absolute right-0 top-12 z-50 hidden w-64 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-700 dark:bg-slate-800">
                            <div class="border-b border-slate-100 px-3 py-2 dark:border-slate-700"><p class="text-xs font-bold dark:text-white">{{ auth()->user()?->name ?? 'Carlos Mendoza' }}</p><p class="truncate text-[11px] text-slate-500 dark:text-slate-400">{{ auth()->user()?->email ?? 'admin@admincorp.local' }}</p></div>
                            <a href="{{ route('profile') }}" class="mt-1 block rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-200 dark:hover:bg-emerald-950/50">◉ Mi perfil</a>
                            <a href="{{ route('blank') }}" class="block rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-700">⚙ Preferencias</a>
                            <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 pt-1">@csrf<button class="w-full rounded-xl px-3 py-2.5 text-left text-xs font-semibold text-red-600 hover:bg-red-50">↪ Cerrar sesión</button></form>
                        </div>
                    </div>
                </div>
            </header>
            <main class="min-h-[calc(100vh-4rem)] p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
    <x-loading-overlay />
    <div id="toast-container" class="pointer-events-none fixed bottom-4 right-4 z-[100] flex w-[calc(100%-2rem)] flex-col gap-3 sm:bottom-6 sm:right-6 sm:w-auto">
        @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
        @if(session('error')) <x-alert type="danger">{{ session('error') }}</x-alert> @endif
    </div>
</body>
<script>
    const profileToggle = document.querySelector('[data-profile-toggle]');
    const profileMenu = document.querySelector('#profile-menu');
    profileToggle?.addEventListener('click', () => {
        const open = profileMenu?.classList.toggle('hidden') === false;
        profileToggle.setAttribute('aria-expanded', String(open));
    });
    document.addEventListener('click', (event) => {
        if (profileMenu && profileToggle && !profileMenu.contains(event.target) && !profileToggle.contains(event.target)) {
            profileMenu.classList.add('hidden');
            profileToggle.setAttribute('aria-expanded', 'false');
        }
    });

    document.querySelectorAll('[data-toast]').forEach((toast) => {
        const close = () => {
            toast.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        };

        toast.querySelector('[data-toast-close]')?.addEventListener('click', close);
        setTimeout(close, 4000);
    });
</script>
</html>
