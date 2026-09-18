<x-layouts.app title="Cartera" eyebrow="Cobranzas">
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
            <div>
                <h2 class="text-2xl font-bold tracking-tight dark:text-white">Cartera de cobranzas</h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Saldos reportados por el último corte.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-badge variant="info">{{ number_format($resumen['deudas']) }} obligaciones</x-badge>
                @can('cartera.import')
                    <x-secondary-button type="button" data-modal-open="cartera-importar">Importar cartera</x-secondary-button>
                @endcan
                <x-secondary-button type="button" data-modal-open="cartera-historial">Ver historial</x-secondary-button>
            </div>
        </div>

        @if(session('success'))
            <x-card class="border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</x-card>
        @endif
        @if(session('error'))
            <x-card class="border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</x-card>
        @endif

        <section class="grid gap-4 sm:grid-cols-3">
            <x-card class="p-5"><p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Saldo reportado</p><p class="mt-2 text-2xl font-bold text-emerald-700 dark:text-emerald-300">Bs {{ number_format($resumen['saldo'], 2, ',', '.') }}</p></x-card>
            <x-card class="p-5"><p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Obligaciones</p><p class="mt-2 text-2xl font-bold dark:text-white">{{ number_format($resumen['deudas']) }}</p></x-card>
            <x-card class="p-5"><p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Clientes</p><p class="mt-2 text-2xl font-bold dark:text-white">{{ number_format($resumen['clientes']) }}</p></x-card>
        </section>

        <x-card class="p-5">
            <form method="GET" action="{{ route('cartera.index') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
                <label class="xl:col-span-2"><span class="sr-only">Buscar</span><input name="buscar" value="{{ request('buscar') }}" placeholder="Cliente, código, documento o teléfono" class="block w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100" /></label>
                <select name="ciudad" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none focus:border-emerald-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"><option value="">Todas las ciudades</option>@foreach($ciudades as $ciudad)<option value="{{ $ciudad }}" @selected(request('ciudad') === $ciudad)>{{ $ciudad }}</option>@endforeach</select>
                <select name="vendedor" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none focus:border-emerald-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"><option value="">Todos los vendedores</option>@foreach($vendedores as $vendedor)<option value="{{ $vendedor }}" @selected(request('vendedor') === $vendedor)>{{ $vendedor }}</option>@endforeach</select>
                <select name="supervisor" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none focus:border-emerald-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"><option value="">Todos los supervisores</option>@foreach($supervisores as $supervisor)<option value="{{ $supervisor }}" @selected(request('supervisor') === $supervisor)>{{ $supervisor }}</option>@endforeach</select>
                <div class="flex gap-2"><select name="estado" class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none focus:border-emerald-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"><option value="">Estado</option>@foreach($estados as $estado)<option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>@endforeach</select><x-primary-button>Filtrar</x-primary-button></div>
            </form>
        </x-card>

        <x-card>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-800/70 dark:text-slate-400"><tr><th class="px-5 py-3">Cliente</th><th class="px-5 py-3">Documento</th><th class="px-5 py-3">Vencimiento</th><th class="px-5 py-3">Responsable</th><th class="px-5 py-3">Estado</th><th class="px-5 py-3 text-right">Saldo reportado</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($deudas as $deuda)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60"><td class="px-5 py-4"><strong class="block text-sm dark:text-white">{{ $deuda->cliente->nombre }}</strong><span class="text-xs text-slate-500 dark:text-slate-400">Código {{ $deuda->cliente->codigo_externo }} · {{ $deuda->cliente->ciudad ?: 'Sin ciudad' }}</span></td><td class="px-5 py-4 font-semibold text-emerald-700 dark:text-emerald-300">{{ $deuda->numero_documento }}</td><td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ $deuda->fecha_vencimiento?->format('d/m/Y') ?: 'Sin fecha' }}</td><td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ $deuda->vendedor_nombre ?: 'Sin asignar' }}</td><td class="px-5 py-4"><x-badge variant="{{ $deuda->estado_origen === 'A' ? 'success' : 'warning' }}">{{ $deuda->estado_origen ?: 'Sin estado' }}</x-badge></td><td class="px-5 py-4 text-right font-bold text-slate-800 dark:text-white">Bs {{ number_format((float) $deuda->saldo_actual, 2, ',', '.') }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-slate-500 dark:text-slate-400">No se encontraron obligaciones con los filtros seleccionados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($deudas->hasPages())<div class="border-t border-slate-100 px-5 py-4 dark:border-slate-800">{{ $deudas->links() }}</div>@endif
        </x-card>

        @can('cartera.import')
            <div id="cartera-importar" data-modal role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="cartera-importar-title" class="fixed inset-0 z-50 hidden overflow-y-auto px-4 py-6 sm:px-0">
                <div data-modal-close class="fixed inset-0 bg-slate-950/75"></div>
                <div class="relative mx-auto mb-6 overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-slate-900 sm:w-full sm:max-w-xl">
                <form method="POST" action="{{ route('cartera.importar') }}" enctype="multipart/form-data" data-loading-form data-loading-message="Importando cartera..." class="p-6">
                    @csrf
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 id="cartera-importar-title" class="text-lg font-bold dark:text-white">Importar nueva cartera</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Seleccione un archivo Excel con el formato autorizado.</p>
                        </div>
                        <button type="button" data-modal-close class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800" aria-label="Cerrar ventana">&times;</button>
                    </div>
                    <div class="mt-6 space-y-2">
                        <label for="archivo" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Archivo Excel</label>
                        <input id="archivo" name="archivo" type="file" accept=".xlsx,.xls" required data-modal-autofocus class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:file:bg-slate-700 dark:file:text-slate-100" />
                        <p class="text-xs text-slate-500 dark:text-slate-400">Se aceptan archivos XLSX o XLS de hasta 20 MB.</p>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <x-secondary-button type="button" data-modal-close>Cancelar</x-secondary-button>
                        <x-primary-button>Importar archivo</x-primary-button>
                    </div>
                </form>
                </div>
            </div>
        @endcan

        <div id="cartera-historial" data-modal role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="cartera-historial-title" class="fixed inset-0 z-50 hidden overflow-y-auto px-4 py-6 sm:px-0">
            <div data-modal-close class="fixed inset-0 bg-slate-950/75"></div>
            <div class="relative mx-auto mb-6 overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-slate-900 sm:w-full sm:max-w-5xl">
            <div class="p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 id="cartera-historial-title" class="text-lg font-bold dark:text-white">Historial de importaciones</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Consulta los últimos documentos procesados.</p>
                    </div>
                    <button type="button" data-modal-close class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800" aria-label="Cerrar ventana">&times;</button>
                </div>
                @if($importaciones->isEmpty())
                    <p class="mt-6 rounded-xl bg-slate-50 p-4 text-sm text-slate-500 dark:bg-slate-800 dark:text-slate-400">Aún no hay importaciones registradas.</p>
                @else
                    <div class="mt-5 max-h-[60vh] overflow-auto">
                        <table class="w-full min-w-[760px] text-left text-sm">
                            <thead class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400"><tr><th class="py-3 pr-4">Archivo</th><th class="py-3 pr-4">Estado</th><th class="py-3 pr-4">Filas procesadas</th><th class="py-3 pr-4">Nuevas</th><th class="py-3 pr-4">Actualizadas</th><th class="py-3 pr-4">Procesado</th></tr></thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($importaciones as $importacion)
                                    <tr><td class="py-3 pr-4 font-medium dark:text-white">{{ $importacion->nombre_archivo }}</td><td class="py-3 pr-4"><x-badge variant="{{ $importacion->estado === 'completada' ? 'success' : 'warning' }}">{{ $importacion->estado ?: 'pendiente' }}</x-badge></td><td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ number_format($importacion->filas_leidas) }}</td><td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ number_format($importacion->deudas_creadas) }}</td><td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ number_format($importacion->deudas_actualizadas) }}</td><td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ $importacion->procesado_en?->format('d/m/Y H:i') ?: 'Pendiente' }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                <div class="mt-6 flex justify-end"><x-secondary-button type="button" data-modal-close>Cerrar</x-secondary-button></div>
            </div>
            </div>
        </div>
    </div>
</x-layouts.app>