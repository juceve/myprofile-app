<?php

namespace Tests\Feature\Console\Commands;

use App\Models\ActualizacionDeuda;
use App\Models\ImportacionCartera;
use App\Models\PresenciaDeudaCorte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ImportarCarteraTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_importa_clientes_y_deudas_desde_un_archivo_excel(): void
    {

        $archivo = $this->crearArchivoCartera([$this->fila('1001', '204469', 22729.99, 7529.99), $this->fila('1001', '204470', 1960, 660)]);

        $this->artisan('cartera:importar', ['archivo' => $archivo])->expectsOutput('Importación completada correctamente.')->assertSuccessful();

        $this->assertDatabaseCount('clientes', 1);
        $this->assertDatabaseCount('deudas', 2);
        $this->assertDatabaseHas('clientes', ['codigo_externo' => '1001', 'nombre' => 'Cliente de prueba']);
        $this->assertDatabaseHas('deudas', ['numero_documento' => '204469', 'saldo_actual' => 7529.99]);
        $this->assertDatabaseHas('importacion_carteras', ['deudas_creadas' => 2, 'deudas_actualizadas' => 0]);
    }

    public function test_actualiza_la_deuda_existente_al_importar_la_misma_clave(): void
    {
        $this->artisan('cartera:importar', ['archivo' => $this->crearArchivoCartera([$this->fila('1001', '204469', 1000, 500)], 'cartera-inicial.xlsx')])->assertSuccessful();
        $this->artisan('cartera:importar', ['archivo' => $this->crearArchivoCartera([$this->fila('1001', '204469', 1000, 200)], 'cartera-actualizada.xlsx')])->assertSuccessful();

        $this->assertDatabaseCount('clientes', 1);
        $this->assertDatabaseCount('deudas', 1);
        $this->assertDatabaseHas('deudas', ['numero_documento' => '204469', 'saldo_actual' => 200]);
        $this->assertDatabaseCount('importacion_carteras', 2);
        $this->assertDatabaseHas('actualizacion_deudas', ['tipo' => 'actualizada']);

        $actualizacion = ActualizacionDeuda::where('tipo', 'actualizada')->firstOrFail();

        $this->assertSame('500.00', $actualizacion->valores_anteriores['saldo_actual']);
        $this->assertSame('200.00', $actualizacion->valores_nuevos['saldo_actual']);
    }

    public function test_compara_cortes_y_registra_ausencias_y_reingresos(): void
    {
        $this->artisan('cartera:importar', ['archivo' => $this->crearArchivoCartera([
            $this->fila('1001', '204469', 1000, 500),
            $this->fila('1001', '204470', 800, 300),
        ], 'corte-1.xlsx')])->assertSuccessful();

        $this->artisan('cartera:importar', ['archivo' => $this->crearArchivoCartera([
            $this->fila('1001', '204469', 1000, 400),
        ], 'corte-2.xlsx')])->assertSuccessful();

        $segundoCorte = ImportacionCartera::where('nombre_archivo', 'corte-2.xlsx')->firstOrFail();
        $this->assertSame(1, $segundoCorte->deudas_ausentes);
        $this->assertSame(0, $segundoCorte->deudas_reingresadas);
        $this->assertDatabaseHas('presencia_deuda_cortes', [
            'importacion_cartera_id' => $segundoCorte->id,
            'estado' => 'ausente',
        ]);

        $this->artisan('cartera:importar', ['archivo' => $this->crearArchivoCartera([
            $this->fila('1001', '204469', 1000, 400),
            $this->fila('1001', '204470', 800, 300),
        ], 'corte-3.xlsx')])->assertSuccessful();

        $tercerCorte = ImportacionCartera::where('nombre_archivo', 'corte-3.xlsx')->firstOrFail();
        $this->assertSame(0, $tercerCorte->deudas_ausentes);
        $this->assertSame(1, $tercerCorte->deudas_reingresadas);
        $this->assertSame('reingresada', PresenciaDeudaCorte::where('importacion_cartera_id', $tercerCorte->id)->where('estado', 'reingresada')->value('estado'));
    }

    public function test_rechaza_un_archivo_que_ya_fue_procesado(): void
    {
        $archivo = $this->crearArchivoCartera([$this->fila('1001', '204469', 1000, 500)]);

        $this->artisan('cartera:importar', ['archivo' => $archivo])->assertSuccessful();
        $this->artisan('cartera:importar', ['archivo' => $archivo])
            ->expectsOutput('El archivo ya fue procesado anteriormente.')
            ->assertFailed();

        $this->assertDatabaseCount('importacion_carteras', 1);
        $this->assertSame('completada', ImportacionCartera::firstOrFail()->estado);
    }

    public function test_omite_filas_sin_campos_requeridos(): void
    {
        $this->artisan('cartera:importar', ['archivo' => $this->crearArchivoCartera([$this->fila('1001', null, 1000, 500)])])->assertSuccessful();

        $this->assertDatabaseCount('clientes', 0);
        $this->assertDatabaseCount('deudas', 0);
    }

    public function test_rechaza_archivos_con_formato_de_excel_distinto(): void
    {
        $ruta = tempnam(sys_get_temp_dir(), 'cartera_invalida_').'.xlsx';
        $libro = new Spreadsheet;
        $libro->getActiveSheet()->fromArray([
            ['Fecha', 'Documento', 'Saldo'],
            [46246, '204469', 500],
        ]);
        (new Xlsx($libro))->save($ruta);

        $this->artisan('cartera:importar', ['archivo' => $ruta])
            ->expectsOutputToContain('Formato de archivo no válido')
            ->assertFailed();

        $rutaConColumnasExtras = tempnam(sys_get_temp_dir(), 'cartera_extra_').'.xlsx';
        $libroExtra = new Spreadsheet;
        $libroExtra->getActiveSheet()->fromArray([
            ['Fecha', 'NumDoc', 'Importe', 'Saldo', 'Cliente', 'CodigoCliente', 'Observacion'],
            [46246, '204469', 1000, 500, 'Cliente de prueba', '1001', 'extra'],
        ]);
        (new Xlsx($libroExtra))->save($rutaConColumnasExtras);

        $this->artisan('cartera:importar', ['archivo' => $rutaConColumnasExtras])
            ->expectsOutputToContain('Formato de archivo no válido')
            ->assertFailed();
    }

    public function test_simulacion_no_guarda_registros(): void
    {
        $this->artisan('cartera:importar', ['archivo' => $this->crearArchivoCartera([$this->fila('1001', '204469', 1000, 500)]), '--empresa' => 'SIMULADA', '--simular' => true])
            ->expectsOutput('Simulación completada. No se guardaron cambios.')
            ->assertSuccessful();

        $this->assertDatabaseCount('empresa_mandantes', 0);
        $this->assertDatabaseCount('clientes', 0);
        $this->assertDatabaseCount('deudas', 0);
    }

    /** @param array<int, array<int, mixed>> $filas */
    private function crearArchivoCartera(array $filas, string $nombre = 'cartera-prueba.xlsx'): string
    {
        Storage::disk('local')->makeDirectory('importaciones');
        $archivo = Storage::disk('local')->path('importaciones/'.$nombre);
        $libro = new Spreadsheet;
        $libro->getActiveSheet()->fromArray([
            ['Fecha', 'NumDoc', 'Importe', 'Saldo', 'Vence', 'Antiguedad', 'Anticuacion', 'Rango', 'Cliente', 'cliLugar', 'entNombreJefeVendedor', 'entNombreSupervisor', 'entNombreVendedor', 'Plazo', 'FechaUltimoPago', 'ciuNombre', 'CodigoCliente', 'LimiteCredito', 'rutId', 'CoordenadaX', 'CoordenadaY', 'Telefono', 'Estado', 'DIRECCION', 'FECHA_CARGA', 'Dias', '%', 'Marca-1', '01 - 30', '31 - 60', '61 - 90', '91 - 120', '121 - 150', '151 - 180', '181-210', '211-250', 'mas 251', 'Mas 60 menor 120', 'total mas de 120 dias', 'total mas de 180 dias', 'Total Vigente', 'Total Vencido', 'Total Cartera', 'Provisión', 'Plazo2', 'Corte Plazos', 'Dif.'],
            ...array_map(fn (array $fila): array => [$fila[0], $fila[1], $fila[2], $fila[3], $fila[4], null, null, null, $fila[5], $fila[21], $fila[6], $fila[7], $fila[8], $fila[9], $fila[10], $fila[11], $fila[12], $fila[13], $fila[14], $fila[15], $fila[16], $fila[17], $fila[18], $fila[19], $fila[20], ...array_fill(0, 22, null)], $filas),
        ]);
        (new Xlsx($libro))->save($archivo);

        return $archivo;
    }

    /** @return array<int, int|float|string|null> */
    private function fila(string $codigoCliente, ?string $numeroDocumento, float $importe, float $saldo): array
    {
        return [46246, $numeroDocumento, $importe, $saldo, 46276, 'Cliente de prueba', 'Jefe de ventas', 'Supervisor de ventas', 'Vendedor de prueba', 30, null, 'Santa Cruz', $codigoCliente, 3000, '123456', -63.161045, -17.738449, '70000000', 'A', 'Dirección de prueba', 46246, 'CIUDAD'];
    }
}
