<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'roles.view', 'group' => 'Roles', 'label' => 'Ver listado'],
            ['name' => 'roles.create', 'group' => 'Roles', 'label' => 'Crear'],
            ['name' => 'roles.update', 'group' => 'Roles', 'label' => 'Modificar'],
            ['name' => 'roles.delete', 'group' => 'Roles', 'label' => 'Eliminar'],
            ['name' => 'users.view', 'group' => 'Usuarios', 'label' => 'Ver listado'],
            ['name' => 'procedures.view', 'group' => 'Trámites', 'label' => 'Ver listado'],
            ['name' => 'procedures.create', 'group' => 'Trámites', 'label' => 'Crear'],
            ['name' => 'settings.view', 'group' => 'Configuración', 'label' => 'Ver listado'],
        ];

        foreach ($permissions as $permissionData) {
            $permission = Permission::findOrCreate($permissionData['name'], 'web');
            $permission->update([
                'group' => $permissionData['group'],
                'label' => $permissionData['label'],
            ]);
        }

        $admin = Role::findOrCreate('Admin', 'web');
        $guest = Role::findOrCreate('Guest', 'web');

        $admin->syncPermissions(Permission::where('guard_name', 'web')->get());
        $guest->syncPermissions([]);
    }
}
