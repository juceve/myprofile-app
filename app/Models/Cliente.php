<?php

namespace App\Models;

use Database\Factories\ClienteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    /** @use HasFactory<ClienteFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'codigo_externo', 'nombre', 'documento_identidad', 'telefono', 'direccion',
        'ciudad', 'tipo_ubicacion', 'longitud', 'latitud', 'limite_credito',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['latitud' => 'decimal:7', 'limite_credito' => 'decimal:2', 'longitud' => 'decimal:7'];
    }

    /** Obligaciones registradas para el cliente. */
    public function deudas(): HasMany
    {
        return $this->hasMany(Deuda::class);
    }
}
