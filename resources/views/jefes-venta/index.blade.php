<x-layouts.app title="Jefes de venta" eyebrow="Configuración">
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h2 class="text-2xl font-bold tracking-tight dark:text-white">Jefes de venta</h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Catálogo histórico de jefes encontrados en los DOC_MADRE.</p>
            </div>
            <x-badge variant="info">{{ number_format($jefesVenta->count()) }} registrados</x-badge>
        </div>

        <x-card class="border-emerald-200 bg-emerald-50/60 p-5 dark:border-emerald-900 dark:bg-emerald-950/20">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Empresa activa</p>
            <p class="mt-1 text-lg font-bold text-emerald-900 dark:text-emerald-100">{{ $empresaMandante?->codigo }} · {{ $empresaMandante?->razon_social }}</p>
        </x-card>

        <x-card class="p-5">
            <form method="GET" action="{{ route('jefes-venta.index') }}" class="flex flex-col gap-3 sm:flex-row">
                <label class="min-w-0 flex-1"><span class="sr-only">Buscar jefe de venta</span><input name="buscar" value="{{ $buscar }}" placeholder="Buscar por nombre" class="block w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100" /></label>
                <x-primary-button>Buscar</x-primary-button>
                @if($buscar)
                    <a href="{{ route('jefes-venta.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Limpiar</a>
                @endif
            </form>
        </x-card>

        <x-card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                        <tr><th class="px-6 py-3 font-semibold">#</th><th class="px-6 py-3 font-semibold">Nombre del jefe de venta</th><th class="px-6 py-3 font-semibold">Registrado</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($jefesVenta as $indice => $jefeVenta)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40"><td class="px-6 py-4 text-slate-500 dark:text-slate-400">{{ $indice + 1 }}</td><td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-200">{{ $jefeVenta->nombre }}</td><td class="px-6 py-4 text-slate-500 dark:text-slate-400">{{ $jefeVenta->created_at?->format('d/m/Y') }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">No hay jefes de venta registrados para la empresa activa.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-layouts.app>
