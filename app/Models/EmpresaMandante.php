<?php

namespace App\Models;

use Database\Factories\EmpresaMandanteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmpresaMandante extends Model
{
    /** @use HasFactory<EmpresaMandanteFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['codigo', 'razon_social', 'activo'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /** Clientes registrados para la empresa mandante. */
    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }

    /** Cortes de cartera recibidos para la empresa mandante. */
    public function importacionesCartera(): HasMany
    {
        return $this->hasMany(ImportacionCartera::class);
    }
}
