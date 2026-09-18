<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportacionCartera extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'usuario_id', 'nombre_archivo', 'hash_archivo', 'archivo_resguardado', 'hoja', 'estado',
        'filas_leidas', 'filas_omitidas', 'clientes_creados', 'clientes_actualizados',
        'deudas_creadas', 'deudas_actualizadas', 'deudas_sin_cambios', 'saldo_reportado', 'procesado_en',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['procesado_en' => 'datetime', 'saldo_reportado' => 'decimal:2'];
    }

    /** Usuario que ejecutó la importación, cuando existe contexto autenticado. */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /** Variaciones de deudas detectadas en este corte. */
    public function actualizacionesDeuda(): HasMany
    {
        return $this->hasMany(ActualizacionDeuda::class);
    }

    /** Deudas presentes en este corte. */
    public function deudas(): HasMany
    {
        return $this->hasMany(Deuda::class);
    }
}
