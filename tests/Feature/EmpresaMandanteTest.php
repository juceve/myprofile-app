<?php

namespace Tests\Feature;

use App\Models\EmpresaMandante;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresaMandanteTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_sin_permiso_no_puede_ver_empresas_mandantes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/empresas-mandantes')->assertForbidden();
    }

    public function test_usuario_con_permiso_puede_listar_empresas_mandantes(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('empresas.view');
        EmpresaMandante::factory()->create(['codigo' => 'BBO', 'razon_social' => 'BBO']);

        $this->actingAs($user)
            ->get('/empresas-mandantes')
            ->assertOk()
            ->assertSee('BBO');
    }

    public function test_usuario_con_permiso_puede_registrar_empresa_mandante(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('empresas.create');

        $this->actingAs($user)
            ->post('/empresas-mandantes', [
                'codigo' => 'bbo',
                'razon_social' => 'BBO',
                'activo' => '1',
            ])
            ->assertRedirect('/empresas-mandantes');

        $this->assertDatabaseHas('empresa_mandantes', ['codigo' => 'BBO', 'razon_social' => 'BBO', 'activo' => true]);
    }

    public function test_usuario_puede_cambiar_la_empresa_activa(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('empresas.view');
        $empresaMandante = EmpresaMandante::factory()->create(['codigo' => 'BBO']);

        $this->actingAs($user)
            ->post('/empresa-mandante/seleccionar', ['empresa_mandante_id' => $empresaMandante->id])
            ->assertRedirect()
            ->assertSessionHas('empresa_mandante_id', $empresaMandante->id);
    }

    public function test_usuario_con_permiso_puede_editar_y_deshabilitar_empresa_mandante(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('empresas.update');
        $empresaMandante = EmpresaMandante::factory()->create(['codigo' => 'BBO']);

        $this->actingAs($user)->withSession(['empresa_mandante_id' => $empresaMandante->id])
            ->put(route('empresas-mandantes.update', $empresaMandante), [
                'codigo' => 'BBO-2',
                'razon_social' => 'BBO Actualizada',
            ])
            ->assertRedirect(route('empresas-mandantes.index'))
            ->assertSessionMissing('empresa_mandante_id');

        $this->assertDatabaseHas('empresa_mandantes', [
            'id' => $empresaMandante->id,
            'codigo' => 'BBO-2',
            'razon_social' => 'BBO Actualizada',
            'activo' => false,
        ]);
    }

    public function test_usuario_sin_permiso_no_puede_editar_empresa_mandante(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $empresaMandante = EmpresaMandante::factory()->create(['codigo' => 'BBO']);

        $this->actingAs($user)
            ->put(route('empresas-mandantes.update', $empresaMandante), [
                'codigo' => 'BBO-2',
                'razon_social' => 'No autorizado',
                'activo' => '1',
            ])
            ->assertForbidden();
    }
}
