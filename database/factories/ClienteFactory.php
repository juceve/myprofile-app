<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\EmpresaMandante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_mandante_id' => EmpresaMandante::factory(),
            'codigo_externo' => fake()->unique()->numerify('CLI-######'),
            'nombre' => fake()->name(),
            'documento_identidad' => fake()->numerify('########'),
            'telefono' => fake()->numerify('7#######'),
            'direccion' => fake()->address(),
            'ciudad' => fake()->city(),
            'tipo_ubicacion' => 'CIUDAD',
            'longitud' => fake()->longitude(-63.3, -63.0),
            'latitud' => fake()->latitude(-17.9, -17.5),
            'limite_credito' => fake()->randomFloat(2, 0, 10000),
        ];
    }
}
