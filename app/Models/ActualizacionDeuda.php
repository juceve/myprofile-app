<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActualizacionDeuda extends Model
{
    /** @var list<string> */
    protected $fillable = ['importacion_cartera_id', 'deuda_id', 'tipo', 'valores_anteriores', 'valores_nuevos'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['valores_anteriores' => 'array', 'valores_nuevos' => 'array'];
    }

    /** Corte DOC_MADRE que detectó la variación. */
    public function importacionCartera(): BelongsTo
    {
        return $this->belongsTo(ImportacionCartera::class);
    }

    /** Obligación cuyos datos externos variaron. */
    public function deuda(): BelongsTo
    {
        return $this->belongsTo(Deuda::class);
    }
}
