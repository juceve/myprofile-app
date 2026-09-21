<?php

namespace App\Models;

use Database\Factories\DeudaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deuda extends Model
{
    /** @use HasFactory<DeudaFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'cliente_id', 'numero_documento', 'fecha_documento', 'fecha_vencimiento',
        'importe_original', 'saldo_actual', 'plazo_dias', 'fecha_ultimo_pago',
        'estado_origen', 'jefe_vendedor_nombre', 'supervisor_nombre', 'vendedor_nombre',
        'fecha_carga', 'origen_archivo', 'origen_fila', 'importacion_cartera_id',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'fecha_carga' => 'date', 'fecha_documento' => 'date', 'fecha_ultimo_pago' => 'date',
            'fecha_vencimiento' => 'date', 'importe_original' => 'decimal:2', 'saldo_actual' => 'decimal:2',
        ];
    }

    /** Cliente titular de la obligación. */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /** Último corte DOC_MADRE que reportó esta obligación. */
    public function importacionCartera(): BelongsTo
    {
        return $this->belongsTo(ImportacionCartera::class);
    }

    /** Historial de presencia de la obligación en los cortes recibidos. */
    public function presenciasCorte(): HasMany
    {
        return $this->hasMany(PresenciaDeudaCorte::class);
    }
}
