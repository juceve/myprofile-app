<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_admin_can_manage_roles_and_permissions(): void
    {
        $this->seed();

        $admin = User::where('nickname', 'admin')->firstOrFail();

        $this->assertTrue($admin->hasRole('Admin'));
        $this->assertDatabaseHas('roles', ['name' => 'Guest', 'guard_name' => 'web']);
        $this->assertDatabaseHas('permissions', ['name' => 'roles.view', 'guard_name' => 'web']);
        $this->assertTrue($admin->can('roles.create'));
        $this->assertTrue($admin->can('roles.update'));
        $this->assertTrue($admin->can('roles.delete'));
    }

    public function test_authorized_user_can_create_role_with_permissions(): void
    {
        $user = User::factory()->create();
        Permission::create(['name' => 'roles.view', 'guard_name' => 'web']);
        Permission::create(['name' => 'roles.create', 'guard_name' => 'web']);
        $permission = Permission::create(['name' => 'reports.view', 'guard_name' => 'web']);
        $user->givePermissionTo(['roles.view', 'roles.create']);

        $response = $this->actingAs($user)->post('/roles', [
            'name' => 'Auditor',
            'permissions' => [$permission->id],
        ]);

        $response->assertRedirect('/roles');
        $role = Role::findByName('Auditor');

        $this->assertTrue($role->hasPermissionTo('reports.view'));
        $this->assertCount(1, $role->permissions);
        $this->assertSame(['reports.view'], $role->permissions->pluck('name')->all());
    }

    public function test_admin_and_guest_roles_cannot_be_renamed_or_deleted(): void
    {
        $this->seed();
        $admin = User::where('nickname', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->put('/roles/'.Role::findByName('Guest')->id, [
                'name' => 'Invitado',
                'permissions' => [],
            ])
            ->assertUnprocessable();

        $this->assertDatabaseHas('roles', ['name' => 'Guest']);

        $this->actingAs($admin)
            ->delete('/roles/'.Role::findByName('Guest')->id)
            ->assertUnprocessable();

        $this->actingAs($admin)
            ->delete('/roles/'.Role::findByName('Admin')->id)
            ->assertUnprocessable();

        $this->assertDatabaseCount('roles', 2);
    }

    public function test_guest_can_modify_permissions_but_admin_is_immutable(): void
    {
        $this->seed();
        $admin = User::where('nickname', 'admin')->firstOrFail();
        $permission = Permission::where('name', 'users.view')->firstOrFail();
        $guest = Role::findByName('Guest');

        $this->actingAs($admin)
            ->put('/roles/'.$guest->id, [
                'name' => 'Guest',
                'permissions' => [$permission->id],
            ])
            ->assertRedirect('/roles');

        $this->assertTrue($guest->fresh()->hasPermissionTo('users.view'));

        $adminRole = Role::findByName('Admin');
        $this->actingAs($admin)
            ->put('/roles/'.$adminRole->id, [
                'name' => 'Admin',
                'permissions' => [],
            ])
            ->assertUnprocessable();

        $this->assertTrue($adminRole->fresh()->hasPermissionTo('roles.view'));
    }
}
