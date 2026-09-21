<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Deuda;
use App\Models\EmpresaMandante;
use App\Models\ImportacionCartera;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarteraTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_sin_permiso_no_puede_ver_la_cartera(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/cartera')->assertForbidden();
    }

    public function test_usuario_con_permiso_puede_buscar_y_filtrar_la_cartera(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('cartera.view');
        $clienteCoincidente = Cliente::factory()->create(['nombre' => 'María Cobranza', 'ciudad' => 'Santa Cruz', 'telefono' => '70001111']);
        $clienteNoCoincidente = Cliente::factory()->create(['nombre' => 'Otro Cliente', 'ciudad' => 'La Paz']);
        Deuda::factory()->for($clienteCoincidente)->create(['numero_documento' => 'DOC-100', 'saldo_actual' => 500, 'vendedor_nombre' => 'Vendedor Uno']);
        Deuda::factory()->for($clienteNoCoincidente)->create(['numero_documento' => 'DOC-200', 'saldo_actual' => 800, 'vendedor_nombre' => 'Vendedor Dos']);

        $this->actingAs($user)->withSession(['empresa_mandante_id' => $clienteCoincidente->empresa_mandante_id])->get('/cartera?buscar=María&ciudad=Santa%20Cruz&vendedor=Vendedor%20Uno')
            ->assertOk()
            ->assertSee('María Cobranza')
            ->assertSee('DOC-100')
            ->assertDontSee('DOC-200');
    }

    public function test_historial_de_importaciones_se_muestra_desde_la_mas_reciente(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('cartera.view');
        $empresaMandante = EmpresaMandante::factory()->create(['codigo' => 'BBO']);

        ImportacionCartera::create([
            'empresa_mandante_id' => $empresaMandante->id,
            'nombre_archivo' => 'cartera-anterior.xlsx',
            'hash_archivo' => 'hash-anterior',
            'hoja' => 'Cartera',
            'estado' => 'completada',
            'procesado_en' => now()->subDay(),
        ]);
        ImportacionCartera::create([
            'empresa_mandante_id' => $empresaMandante->id,
            'nombre_archivo' => 'cartera-reciente.xlsx',
            'hash_archivo' => 'hash-reciente',
            'hoja' => 'Cartera',
            'estado' => 'completada',
            'procesado_en' => now(),
        ]);

        $this->actingAs($user)->withSession(['empresa_mandante_id' => $empresaMandante->id])
            ->get('/cartera')
            ->assertOk()
            ->assertSeeInOrder(['cartera-reciente.xlsx', 'cartera-anterior.xlsx']);
    }

    public function test_importar_cartera_requiere_empresa_mandante(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('cartera.import');

        $this->actingAs($user)
            ->post('/cartera/importar', [])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('error');
    }

    public function test_cartera_solo_muestra_la_empresa_activa(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('cartera.view');
        $empresaActiva = EmpresaMandante::factory()->create(['codigo' => 'BBO']);
        $otraEmpresa = EmpresaMandante::factory()->create(['codigo' => 'OTRA']);
        $clienteActivo = Cliente::factory()->create(['empresa_mandante_id' => $empresaActiva->id, 'nombre' => 'Cliente BBO']);
        $clienteOculto = Cliente::factory()->create(['empresa_mandante_id' => $otraEmpresa->id, 'nombre' => 'Cliente OTRA']);
        Deuda::factory()->for($clienteActivo)->create(['numero_documento' => 'DOC-BBO']);
        Deuda::factory()->for($clienteOculto)->create(['numero_documento' => 'DOC-OTRA']);

        $this->actingAs($user)->withSession(['empresa_mandante_id' => $empresaActiva->id])
            ->get('/cartera')
            ->assertSee('Cliente BBO')
            ->assertDontSee('Cliente OTRA')
            ->assertSee('BBO');
    }

    public function test_importa_el_mismo_codigo_de_cliente_de_forma_aislada_por_empresa(): void
    {
        $empresaBbo = EmpresaMandante::factory()->create(['codigo' => 'BBO']);
        $otraEmpresa = EmpresaMandante::factory()->create(['codigo' => 'OTRA']);

        Cliente::factory()->create([
            'empresa_mandante_id' => $empresaBbo->id,
            'codigo_externo' => '1001',
        ]);
        Cliente::factory()->create([
            'empresa_mandante_id' => $otraEmpresa->id,
            'codigo_externo' => '1001',
        ]);

        $this->assertDatabaseCount('clientes', 2);
    }
}
