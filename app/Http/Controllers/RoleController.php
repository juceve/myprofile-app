<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('roles.index', [
            'roles' => Role::with('permissions')->where('guard_name', 'web')->orderBy('name')->get(),
            'permissions' => Permission::where('guard_name', 'web')->orderBy('group')->orderBy('label')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('roles')->where('guard_name', 'web')],
            'permissions' => ['array'],
            'permissions.*' => [
                'integer',
                Rule::exists('permissions', 'id')->where(fn ($query) => $query->where('guard_name', 'web')),
            ],
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
        $role->syncPermissions($this->permissionsFromIds($validated['permissions'] ?? []));

        return to_route('roles.index')->with('success', 'Rol creado correctamente.');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_unless($role->guard_name === 'web', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('roles')->ignore($role->id)->where('guard_name', 'web')],
            'permissions' => ['array'],
            'permissions.*' => [
                'integer',
                Rule::exists('permissions', 'id')->where(fn ($query) => $query->where('guard_name', 'web')),
            ],
        ]);

        abort_if($this->isProtectedRole($role) && $validated['name'] !== $role->name, 422, 'Los roles Admin y Guest no pueden renombrarse.');
        abort_if($role->name === 'Admin', 422, 'El rol Admin no puede modificarse.');

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($this->permissionsFromIds($validated['permissions'] ?? []));

        return to_route('roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_unless($role->guard_name === 'web', 404);
        abort_if($this->isProtectedRole($role), 422, 'Los roles Admin y Guest están protegidos.');

        $role->delete();

        return to_route('roles.index')->with('success', 'Rol eliminado correctamente.');
    }

    public function storePermission(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('permissions')->where('guard_name', 'web')],
            'group' => ['required', 'string', 'max:100'],
            'label' => ['nullable', 'string', 'max:100'],
        ]);

        Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
            'group' => $validated['group'],
            'label' => $validated['label'] ?: $validated['name'],
        ]);

        return to_route('roles.index')->with('success', 'Permiso creado correctamente.');
    }

    public function updatePermission(Request $request, Permission $permission): RedirectResponse
    {
        abort_unless($permission->guard_name === 'web', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('permissions')->ignore($permission->id)->where('guard_name', 'web')],
            'group' => ['required', 'string', 'max:100'],
            'label' => ['nullable', 'string', 'max:100'],
        ]);

        $permission->update($validated);

        return to_route('roles.index')->with('success', 'Permiso actualizado correctamente.');
    }

    public function destroyPermission(Permission $permission): RedirectResponse
    {
        abort_unless($permission->guard_name === 'web', 404);
        $permission->delete();

        return to_route('roles.index')->with('success', 'Permiso eliminado correctamente.');
    }

    /**
     * Resolve checkbox IDs into permission models accepted by Spatie.
     *
     * @param  array<int, int|string>  $ids
     */
    private function permissionsFromIds(array $ids): Collection
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('id', $ids)
            ->get();
    }

    private function isProtectedRole(Role $role): bool
    {
        return in_array($role->name, ['Admin', 'Guest'], true);
    }
}
