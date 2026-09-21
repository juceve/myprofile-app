<x-layouts.app title="Empresas mandantes" eyebrow="Configuración">
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h2 class="text-2xl font-bold tracking-tight dark:text-white">Empresas mandantes</h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Administra las empresas propietarias de las carteras recibidas.</p>
            </div>
            @can('empresas.create')
                <a href="#registrar-empresa" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    Registrar empresa
                </a>
            @endcan
        </div>

        @if($errors->any())
            <x-alert type="danger">{{ $errors->first() }}</x-alert>
        @endif

        <div class="grid gap-4 sm:grid-cols-3">
            <x-card class="p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Empresas registradas</p>
                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $empresasMandantes->count() }}</p>
            </x-card>
            <x-card class="p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Empresas activas</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $empresasMandantes->where('activo', true)->count() }}</p>
            </x-card>
            <x-card class="p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Cortes recibidos</p>
                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $empresasMandantes->sum('importaciones_cartera_count') }}</p>
            </x-card>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <x-card class="overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 dark:border-slate-800">
                    <div>
                        <h3 class="font-bold dark:text-white">Listado de empresas</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Cada empresa mantiene su propia cartera de clientes y obligaciones.</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Código</th>
                                <th class="px-6 py-3 font-semibold">Razón social</th>
                                <th class="px-6 py-3 font-semibold">Clientes</th>
                                <th class="px-6 py-3 font-semibold">Cortes</th>
                                <th class="px-6 py-3 font-semibold">Estado</th>
                                <th class="px-6 py-3 text-right font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($empresasMandantes as $empresaMandante)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                    <td class="whitespace-nowrap px-6 py-4 font-bold text-emerald-700 dark:text-emerald-300">{{ $empresaMandante->codigo }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-800 dark:text-slate-200">{{ $empresaMandante->razon_social }}</td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ number_format($empresaMandante->clientes_count) }}</td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ number_format($empresaMandante->importaciones_cartera_count) }}</td>
                                    <td class="px-6 py-4">
                                        <x-badge :variant="$empresaMandante->activo ? 'success' : 'neutral'">{{ $empresaMandante->activo ? 'Activa' : 'Inactiva' }}</x-badge>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @can('empresas.update')
                                            <details data-floating-menu class="relative inline-block text-left">
                                                <summary data-floating-menu-trigger class="inline-flex cursor-pointer list-none items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                                    <i class="fa-solid fa-gear" aria-hidden="true"></i>
                                                    Administrar
                                                </summary>
                                                <div data-floating-menu-panel class="fixed z-[100] hidden w-72 rounded-xl border border-slate-200 bg-white p-4 text-left shadow-xl dark:border-slate-700 dark:bg-slate-900">
                                                    <form method="POST" action="{{ route('empresas-mandantes.update', $empresaMandante) }}" class="space-y-3">
                                                        @csrf @method('PUT')
                                                        <div>
                                                            <x-input-label for="codigo-{{ $empresaMandante->id }}" value="Código" />
                                                            <x-text-input id="codigo-{{ $empresaMandante->id }}" name="codigo" value="{{ $empresaMandante->codigo }}" class="mt-1 block w-full uppercase" maxlength="50" required />
                                                        </div>
                                                        <div>
                                                            <x-input-label for="razon-social-{{ $empresaMandante->id }}" value="Razón social" />
                                                            <x-text-input id="razon-social-{{ $empresaMandante->id }}" name="razon_social" value="{{ $empresaMandante->razon_social }}" class="mt-1 block w-full" maxlength="255" required />
                                                        </div>
                                                        <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                                                            <input type="checkbox" name="activo" value="1" @checked($empresaMandante->activo) class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-800">
                                                            Empresa habilitada para selección
                                                        </label>
                                                        <x-primary-button class="w-full justify-center">Guardar cambios</x-primary-button>
                                                    </form>
                                                </div>
                                            </details>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">No hay empresas mandantes registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

            @can('empresas.create')
                <x-card id="registrar-empresa" class="p-6">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300"><i class="fa-solid fa-building" aria-hidden="true"></i></span>
                        <div>
                            <h3 class="font-bold dark:text-white">Registrar empresa</h3>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">El código identificará su cartera.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('empresas-mandantes.store') }}" class="mt-6 space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="codigo" value="Código" />
                            <x-text-input id="codigo" name="codigo" value="{{ old('codigo') }}" class="mt-1 block w-full uppercase" placeholder="Ej. BBO" maxlength="50" required />
                        </div>
                        <div>
                            <x-input-label for="razon_social" value="Razón social" />
                            <x-text-input id="razon_social" name="razon_social" value="{{ old('razon_social') }}" class="mt-1 block w-full" placeholder="Nombre de la empresa" maxlength="255" required />
                        </div>
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input type="checkbox" name="activo" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-800">
                            Empresa activa
                        </label>
                        <x-primary-button class="w-full justify-center">Registrar empresa</x-primary-button>
                    </form>
                </x-card>
            @endcan
        </div>
    </div>
</x-layouts.app>
