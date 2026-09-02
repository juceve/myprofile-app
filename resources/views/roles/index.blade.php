<x-layouts.app title="Roles y permisos" eyebrow="Gestión institucional">
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h2 class="text-2xl font-bold tracking-tight dark:text-white">Administración de roles</h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Crea roles, asigna permisos y controla el acceso a los módulos.</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <x-card class="p-6 xl:col-span-2">
                <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                    <div><h3 class="font-bold dark:text-white">Roles existentes</h3><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $roles->count() }} roles configurados</p></div>
                    <x-badge variant="info">Guard: web</x-badge>
                </div>
                <div class="space-y-4">
                    @foreach($roles as $role)
                        <details class="group rounded-2xl border border-slate-200 dark:border-slate-700">
                            <summary class="flex cursor-pointer list-none items-center gap-3 px-4 py-4">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300"><i class="fa-solid fa-shield-halved"></i></span>
                                <span class="flex-1"><strong class="block text-sm dark:text-white">{{ $role->name }}</strong><small class="text-xs text-slate-500 dark:text-slate-400">{{ $role->permissions->count() }} permisos asignados</small></span>
                                <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition group-open:rotate-180"></i>
                            </summary>
                            <div class="border-t border-slate-100 p-4 dark:border-slate-800">
                                <form method="POST" action="{{ route('roles.update', $role) }}" class="space-y-4">
                                    @csrf @method('PUT')
                                    <div><x-input-label for="role-{{ $role->id }}" value="Nombre del rol" /><x-text-input id="role-{{ $role->id }}" name="name" value="{{ $role->name }}" class="mt-1 block w-full" readonly="{{ in_array($role->name, ['Admin', 'Guest'], true) ? 'readonly' : '' }}" disabled="{{ $role->name === 'Admin' ? 'disabled' : '' }}" required /></div>
                                    <fieldset><legend class="mb-2 text-sm font-semibold text-slate-700 dark:text-slate-300">Permisos</legend><div class="space-y-3">@foreach($permissions->groupBy('group') as $group => $groupPermissions)<div><p class="mb-2 text-xs font-bold uppercase tracking-wide text-emerald-700 dark:text-emerald-400">{{ $group }}</p><div class="grid gap-2 sm:grid-cols-2">@foreach($groupPermissions as $permission)<label class="flex items-center gap-2 rounded-lg border border-slate-100 px-3 py-2 text-xs dark:border-slate-800"><input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked($role->hasPermissionTo($permission)) @disabled($role->name === 'Admin') class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-800"><span class="dark:text-slate-300">{{ $permission->label ?: $permission->name }}</span></label>@endforeach</div></div>@endforeach</div></fieldset>
                                    @if($role->name !== 'Admin') <div class="flex flex-wrap gap-2"><x-primary-button>Guardar cambios</x-primary-button></div> @else <span class="block text-xs text-slate-400">Rol protegido completamente</span> @endif
                                </form>
                                @if(! in_array($role->name, ['Admin', 'Guest'], true))
                                    <x-confirm-form :action="route('roles.destroy', $role)" method="DELETE" title="¿Eliminar este rol?" text="Esta acción no se puede deshacer." confirm-text="Eliminar" cancel-text="Cancelar" class="mt-2">
                                        <x-danger-button type="submit">Eliminar</x-danger-button>
                                    </x-confirm-form>
                                @else
                                    <span class="mt-2 block text-xs text-slate-400">Nombre y eliminación protegidos</span>
                                @endif
                            </div>
                        </details>
                    @endforeach
                </div>
            </x-card>

            <div class="space-y-6">
                <x-card class="p-6">
                    <h3 class="font-bold dark:text-white">Crear nuevo rol</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Define el acceso inicial del nuevo rol.</p>
                    <form method="POST" action="{{ route('roles.store') }}" class="mt-5 space-y-4">@csrf<x-input label="Nombre del rol" name="name" placeholder="Ej. Auditor" /><fieldset><legend class="mb-2 text-sm font-semibold text-slate-700 dark:text-slate-300">Permisos iniciales</legend><div class="space-y-3">@foreach($permissions->groupBy('group') as $group => $groupPermissions)<div><p class="mb-2 text-xs font-bold uppercase tracking-wide text-emerald-700 dark:text-emerald-400">{{ $group }}</p><div class="space-y-2">@foreach($groupPermissions as $permission)<label class="flex items-center gap-2 text-xs"><input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-800"><span class="dark:text-slate-300">{{ $permission->label ?: $permission->name }}</span></label>@endforeach</div></div>@endforeach</div></fieldset><x-primary-button>Crear rol</x-primary-button></form>
                </x-card>
                <x-card class="p-6" style="display:none;">
                    <h3 class="font-bold dark:text-white">Nuevo permiso</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Usa nombres agrupados, por ejemplo: reports.export.</p>
                    <form method="POST" action="{{ route('permissions.store') }}" class="mt-5 space-y-3">@csrf<div class="grid gap-3 sm:grid-cols-2"><x-input label="Nombre técnico" name="name" placeholder="modulo.accion" /><x-input label="Grupo" name="group" placeholder="Usuarios" /><x-input label="Título visible" name="label" placeholder="Ver listado" /></div><x-primary-button>Agregar permiso</x-primary-button></form>
                    <div class="mt-5 space-y-2">@foreach($permissions->groupBy('group') as $group => $groupPermissions)<details class="rounded-xl border border-slate-200 p-3 dark:border-slate-700"><summary class="cursor-pointer text-xs font-bold dark:text-white">{{ $group }} <span class="text-slate-400">({{ $groupPermissions->count() }})</span></summary><div class="mt-3 space-y-3">@foreach($groupPermissions as $permission)<form method="POST" action="{{ route('permissions.update', $permission) }}" class="grid gap-2 sm:grid-cols-[1fr_1fr_1fr_auto]">@csrf @method('PUT')<x-text-input name="name" value="{{ $permission->name }}" aria-label="Nombre técnico" required /><x-text-input name="group" value="{{ $permission->group }}" aria-label="Grupo" required /><x-text-input name="label" value="{{ $permission->label }}" aria-label="Título visible" /><div class="flex gap-1"><x-primary-button title="Guardar permiso"><i class="fa-solid fa-check"></i></x-primary-button></form><form method="POST" action="{{ route('permissions.destroy', $permission) }}" onsubmit="return confirm('¿Eliminar este permiso?')">@csrf @method('DELETE')<x-danger-button type="submit" title="Eliminar permiso"><i class="fa-solid fa-trash"></i></x-danger-button></form></div>@endforeach</div></details>@endforeach</div>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
