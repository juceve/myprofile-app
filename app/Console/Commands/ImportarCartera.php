<?php

namespace App\Console\Commands;

use App\Models\ActualizacionDeuda;
use App\Models\Cliente;
use App\Models\Deuda;
use App\Models\EmpresaMandante;
use App\Models\ImportacionCartera;
use App\Models\PresenciaDeudaCorte;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

#[Signature('cartera:importar {archivo=DOC_MADRE.xlsx : Ruta del archivo XLSX} {--empresa=BBO : Código de la empresa mandante} {--simular : Valida sin guardar cambios}')]
#[Description('Importa clientes y deudas desde un archivo Excel de cartera.')]
class ImportarCartera extends Command
{
    /** @var array<int, true> */
    private array $deudasPresentes = [];

    /**
     * Importa clientes y obligaciones preservando la fila de origen.
     */
    public function handle(): int
    {
        $archivo = $this->resolverArchivo((string) $this->argument('archivo'));

        if ($archivo === null) {
            $this->error('No se encontró el archivo de cartera indicado.');

            return self::FAILURE;
        }

        try {
            $lector = IOFactory::createReaderForFile($archivo);
            $lector->setReadDataOnly(true);
            $hashArchivo = hash_file('sha256', $archivo);

            if (! $this->option('simular') && ImportacionCartera::where('hash_archivo', $hashArchivo)->exists()) {
                $this->error('El archivo ya fue procesado anteriormente.');

                return self::FAILURE;
            }

            $archivoResguardado = $this->option('simular') ? null : $this->resguardarArchivo($archivo, $hashArchivo);
            $resultado = $this->importar(
                $lector->load($archivo)->getActiveSheet(),
                basename($archivo),
                $hashArchivo,
                $archivoResguardado,
                (string) $this->option('empresa'),
                (bool) $this->option('simular'),
            );
        } catch (Throwable $exception) {
            report($exception);
            $this->error('No fue posible importar la cartera: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->table(['Resultado', 'Cantidad'], collect($resultado)->map(fn (int $cantidad, string $clave): array => [Str::headline($clave), $cantidad])->all());
        $this->info($this->option('simular') ? 'Simulación completada. No se guardaron cambios.' : 'Importación completada correctamente.');

        return self::SUCCESS;
    }

    private function resolverEmpresaMandante(string $codigo): EmpresaMandante
    {
        $codigo = Str::upper(Str::squish($codigo));

        if ($codigo === '') {
            throw new \InvalidArgumentException('Debe indicar una empresa mandante válida.');
        }

        return EmpresaMandante::firstOrCreate(['codigo' => $codigo], ['razon_social' => $codigo]);
    }

    private function resolverArchivo(string $archivo): ?string
    {
        foreach ([$archivo, base_path($archivo)] as $ruta) {
            if (is_file($ruta)) {
                return $ruta;
            }
        }

        return null;
    }

    private function resguardarArchivo(string $archivo, string $hashArchivo): string
    {
        $extension = pathinfo($archivo, PATHINFO_EXTENSION);
        $ruta = "cartera/importaciones/{$hashArchivo}.{$extension}";

        Storage::disk('local')->put($ruta, file_get_contents($archivo));

        return $ruta;
    }

    /** @return array<string, int> */
    private function importar(Worksheet $hoja, string $archivo, string $hashArchivo, ?string $archivoResguardado, string $codigoEmpresaMandante, bool $simular): array
    {
        $columnas = $this->columnas($hoja);
        $this->verificarColumnas($columnas);

        $resultado = ['filas_leidas' => 0, 'filas_omitidas' => 0, 'clientes_creados' => 0, 'clientes_actualizados' => 0, 'deudas_creadas' => 0, 'deudas_actualizadas' => 0, 'deudas_sin_cambios' => 0, 'saldo_reportado' => 0];
        $this->deudasPresentes = [];

        DB::beginTransaction();

        try {
            $empresaMandante = $this->resolverEmpresaMandante($codigoEmpresaMandante);
            $importacionAnterior = ImportacionCartera::query()
                ->where('empresa_mandante_id', $empresaMandante->id)
                ->where('estado', 'completada')
                ->orderByDesc('procesado_en')
                ->orderByDesc('id')
                ->first();
            $importacion = ImportacionCartera::create([
                'empresa_mandante_id' => $empresaMandante->id,
                'nombre_archivo' => $archivo,
                'hash_archivo' => $hashArchivo,
                'archivo_resguardado' => $archivoResguardado,
                'hoja' => $hoja->getTitle(),
            ]);

            foreach (range(2, $hoja->getHighestDataRow()) as $numeroFila) {
                $resultado['filas_leidas']++;
                $fila = $hoja->rangeToArray("A{$numeroFila}:AU{$numeroFila}", null, true, false)[0];

                try {
                    $this->validarFila($fila, $columnas);
                    $cliente = $this->importarCliente($fila, $columnas, $empresaMandante);
                    $resultado[$cliente->wasRecentlyCreated ? 'clientes_creados' : 'clientes_actualizados']++;
                    $resultado['saldo_reportado'] += (float) $this->requerido($fila, $columnas, 'Saldo');
                    $resultado[$this->importarDeuda($fila, $columnas, $cliente->id, $archivo, $numeroFila, $importacion)]++;
                } catch (\InvalidArgumentException) {
                    $resultado['filas_omitidas']++;
                }
            }

            $comparacion = $this->registrarComparacion($importacion, $importacionAnterior);
            $resultado = [...$resultado, ...$comparacion];

            $importacion->update([
                ...$resultado,
                ...$comparacion,
                'estado' => 'completada',
                'procesado_en' => now(),
            ]);
            $simular ? DB::rollBack() : DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }

        return $resultado;
    }

    /** @return array{deudas_ausentes: int, deudas_reingresadas: int} */
    private function registrarComparacion(ImportacionCartera $importacion, ?ImportacionCartera $importacionAnterior): array
    {
        $deudasAnteriores = $importacionAnterior?->presenciasDeuda()
            ->whereIn('estado', ['presente', 'reingresada'])
            ->pluck('deuda_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all() ?? [];
        $deudasAusentesAnteriores = $importacionAnterior?->presenciasDeuda()
            ->where('estado', 'ausente')
            ->pluck('deuda_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all() ?? [];

        foreach (array_keys($this->deudasPresentes) as $deudaId) {
            PresenciaDeudaCorte::create([
                'importacion_cartera_id' => $importacion->id,
                'deuda_id' => $deudaId,
                'estado' => in_array($deudaId, $deudasAusentesAnteriores, true) ? 'reingresada' : 'presente',
            ]);
        }

        $deudasAusentes = array_diff($deudasAnteriores, array_keys($this->deudasPresentes));

        foreach ($deudasAusentes as $deudaId) {
            PresenciaDeudaCorte::create([
                'importacion_cartera_id' => $importacion->id,
                'deuda_id' => $deudaId,
                'estado' => 'ausente',
            ]);
        }

        return [
            'deudas_ausentes' => count($deudasAusentes),
            'deudas_reingresadas' => count(array_intersect(array_keys($this->deudasPresentes), $deudasAusentesAnteriores)),
        ];
    }

    /** @param array<int, mixed> $fila @param array<string, int> $columnas */
    private function validarFila(array $fila, array $columnas): void
    {
        foreach (['CodigoCliente', 'Cliente', 'NumDoc', 'Importe', 'Saldo'] as $campo) {
            $this->requerido($fila, $columnas, $campo);
        }

        if ($this->fecha($this->requerido($fila, $columnas, 'Fecha')) === null) {
            throw new \InvalidArgumentException('La fecha del documento no es válida.');
        }
    }

    /** @return array<string, int> */
    private function columnas(Worksheet $hoja): array
    {
        return collect($hoja->rangeToArray('A1:AU1', null, true, false)[0])
            ->filter(fn (mixed $encabezado): bool => $this->texto($encabezado) !== '')
            ->mapWithKeys(fn (mixed $encabezado, int $indice): array => [$this->texto($encabezado) => $indice])
            ->all();
    }

    /** @param array<string, int> $columnas */
    private function verificarColumnas(array $columnas): void
    {
        $esperadas = [
            'Fecha',
            'NumDoc',
            'Importe',
            'Saldo',
            'Vence',
            'Antiguedad',
            'Anticuacion',
            'Rango',
            'Cliente',
            'cliLugar',
            'entNombreJefeVendedor',
            'entNombreSupervisor',
            'entNombreVendedor',
            'Plazo',
            'FechaUltimoPago',
            'ciuNombre',
            'CodigoCliente',
            'LimiteCredito',
            'rutId',
            'CoordenadaX',
            'CoordenadaY',
            'Telefono',
            'Estado',
            'DIRECCION',
            'FECHA_CARGA',
            'Dias',
            '%',
            'Marca-1',
            '01 - 30',
            '31 - 60',
            '61 - 90',
            '91 - 120',
            '121 - 150',
            '151 - 180',
            '181-210',
            '211-250',
            'mas 251',
            'Mas 60 menor 120',
            'total mas de 120 dias',
            'total mas de 180 dias',
            'Total Vigente',
            'Total Vencido',
            'Total Cartera',
            'Provisión',
            'Plazo2',
            'Corte Plazos',
            'Dif.',
        ];
        $actuales = array_keys($columnas);
        $faltantes = array_diff($esperadas, $actuales);
        $extras = array_diff($actuales, $esperadas);

        if ($faltantes !== [] || $extras !== []) {
            throw new \InvalidArgumentException('Formato de archivo no válido. Debe incluir exactamente estas columnas: '.implode(', ', $esperadas).'.');
        }
    }

    /** @param array<int, mixed> $fila @param array<string, int> $columnas */
    private function importarCliente(array $fila, array $columnas, EmpresaMandante $empresaMandante): Cliente
    {
        return Cliente::updateOrCreate([
            'empresa_mandante_id' => $empresaMandante->id,
            'codigo_externo' => $this->requerido($fila, $columnas, 'CodigoCliente'),
        ], [
            'nombre' => $this->requerido($fila, $columnas, 'Cliente'), 'documento_identidad' => $this->valor($fila, $columnas, 'rutId'),
            'telefono' => $this->valor($fila, $columnas, 'Telefono'), 'direccion' => $this->valor($fila, $columnas, 'DIRECCION'),
            'ciudad' => $this->valor($fila, $columnas, 'ciuNombre'), 'tipo_ubicacion' => $this->valor($fila, $columnas, 'cliLugar'),
            'longitud' => $this->decimal($this->valor($fila, $columnas, 'CoordenadaX')), 'latitud' => $this->decimal($this->valor($fila, $columnas, 'CoordenadaY')),
            'limite_credito' => $this->decimal($this->valor($fila, $columnas, 'LimiteCredito')) ?? 0,
        ]);
    }

    /** @param array<int, mixed> $fila @param array<string, int> $columnas */
    private function importarDeuda(array $fila, array $columnas, int $clienteId, string $archivo, int $numeroFila, ImportacionCartera $importacion): string
    {
        $fechaDocumento = $this->fecha($this->requerido($fila, $columnas, 'Fecha'));

        if ($fechaDocumento === null) {
            throw new \InvalidArgumentException('La fecha del documento no es válida.');
        }

        $deuda = Deuda::query()
            ->where('cliente_id', $clienteId)
            ->where('numero_documento', $this->requerido($fila, $columnas, 'NumDoc'))
            ->whereDate('fecha_documento', $fechaDocumento)
            ->firstOrNew();

        $esNueva = ! $deuda->exists;
        $valoresAnteriores = $esNueva ? null : $this->valoresAuditables($deuda);
        $deuda->fill([
            'cliente_id' => $clienteId,
            'numero_documento' => $this->requerido($fila, $columnas, 'NumDoc'),
            'fecha_documento' => $fechaDocumento,
            'fecha_vencimiento' => $this->fecha($this->valor($fila, $columnas, 'Vence')), 'importe_original' => $this->decimal($this->requerido($fila, $columnas, 'Importe')),
            'saldo_actual' => $this->decimal($this->requerido($fila, $columnas, 'Saldo')), 'plazo_dias' => $this->entero($this->valor($fila, $columnas, 'Plazo')),
            'fecha_ultimo_pago' => $this->fecha($this->valor($fila, $columnas, 'FechaUltimoPago')), 'estado_origen' => $this->valor($fila, $columnas, 'Estado'),
            'jefe_vendedor_nombre' => $this->valor($fila, $columnas, 'entNombreJefeVendedor'), 'supervisor_nombre' => $this->valor($fila, $columnas, 'entNombreSupervisor'),
            'vendedor_nombre' => $this->valor($fila, $columnas, 'entNombreVendedor'), 'fecha_carga' => $this->fecha($this->valor($fila, $columnas, 'FECHA_CARGA')),
            'origen_archivo' => $archivo, 'origen_fila' => $numeroFila,
            'importacion_cartera_id' => $importacion->id,
        ]);
        $cambio = $esNueva || $deuda->isDirty($this->camposAuditables());
        $deuda->save();
        $this->deudasPresentes[$deuda->id] = true;

        if ($cambio) {
            ActualizacionDeuda::create([
                'importacion_cartera_id' => $importacion->id,
                'deuda_id' => $deuda->id,
                'tipo' => $valoresAnteriores === null ? 'creada' : 'actualizada',
                'valores_anteriores' => $valoresAnteriores,
                'valores_nuevos' => $this->valoresAuditables($deuda),
            ]);
        }

        return $esNueva ? 'deudas_creadas' : ($cambio ? 'deudas_actualizadas' : 'deudas_sin_cambios');
    }

    /** @return list<string> */
    private function camposAuditables(): array
    {
        return [
            'fecha_vencimiento', 'importe_original', 'saldo_actual', 'plazo_dias', 'fecha_ultimo_pago',
            'estado_origen', 'jefe_vendedor_nombre', 'supervisor_nombre', 'vendedor_nombre', 'fecha_carga',
        ];
    }

    /** @return array<string, mixed> */
    private function valoresAuditables(Deuda $deuda): array
    {
        return $deuda->only($this->camposAuditables());
    }

    /** @param array<int, mixed> $fila @param array<string, int> $columnas */
    private function requerido(array $fila, array $columnas, string $nombre): string
    {
        return $this->valor($fila, $columnas, $nombre) ?? throw new \InvalidArgumentException("El campo {$nombre} es obligatorio.");
    }

    /** @param array<int, mixed> $fila @param array<string, int> $columnas */
    private function valor(array $fila, array $columnas, string $nombre): ?string
    {
        $valor = $this->texto($fila[$columnas[$nombre]] ?? null);

        return $valor === '' ? null : $valor;
    }

    private function texto(mixed $valor): string
    {
        return Str::squish((string) $valor);
    }

    private function decimal(?string $valor): ?string
    {
        return $valor === null || ! is_numeric($valor) ? null : number_format((float) $valor, 2, '.', '');
    }

    private function entero(?string $valor): ?int
    {
        return $valor === null || filter_var($valor, FILTER_VALIDATE_INT) === false ? null : (int) $valor;
    }

    private function fecha(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        try {
            return is_numeric($valor) ? Carbon::instance(Date::excelToDateTimeObject((float) $valor))->toDateString() : Carbon::parse($valor)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
