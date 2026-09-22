<?php

namespace Tests\Feature;

use App\Models\EmpresaMandante;
use App\Models\JefeVenta;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JefeVentaTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_con_permiso_ve_solo_los_jefes_de_la_empresa_activa(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('empresas.view');
        $empresaActiva = EmpresaMandante::factory()->create(['codigo' => 'BBO']);
        $otraEmpresa = EmpresaMandante::factory()->create(['codigo' => 'OTRA']);
        JefeVenta::factory()->create(['empresa_mandante_id' => $empresaActiva->id, 'nombre' => 'Jefe BBO']);
        JefeVenta::factory()->create(['empresa_mandante_id' => $otraEmpresa->id, 'nombre' => 'Jefe OTRA']);

        $this->actingAs($user)->withSession(['empresa_mandante_id' => $empresaActiva->id])
            ->get('/jefes-venta')
            ->assertOk()
            ->assertSee('Jefe BBO')
            ->assertDontSee('Jefe OTRA');
    }

    public function test_usuario_sin_permiso_no_puede_ver_jefes_de_venta(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/jefes-venta')->assertForbidden();
    }
}
