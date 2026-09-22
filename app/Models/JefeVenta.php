<?php

namespace App\Models;

use Database\Factories\JefeVentaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JefeVenta extends Model
{
    /** @use HasFactory<JefeVentaFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['empresa_mandante_id', 'nombre'];

    /** Empresa mandante a la que pertenece el jefe de ventas. */
    public function empresaMandante(): BelongsTo
    {
        return $this->belongsTo(EmpresaMandante::class);
    }
}
