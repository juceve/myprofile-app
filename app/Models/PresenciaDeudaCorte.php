<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresenciaDeudaCorte extends Model
{
    /** @var list<string> */
    protected $fillable = ['importacion_cartera_id', 'deuda_id', 'estado'];

    /** Corte que registró la presencia de la obligación. */
    public function importacionCartera(): BelongsTo
    {
        return $this->belongsTo(ImportacionCartera::class);
    }

    /** Obligación comparada en el corte. */
    public function deuda(): BelongsTo
    {
        return $this->belongsTo(Deuda::class);
    }
}
