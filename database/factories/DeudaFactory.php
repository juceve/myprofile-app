<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Deuda;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deuda>
 */
class DeudaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'numero_documento' => fake()->unique()->numerify('DOC-######'),
            'fecha_documento' => fake()->date(),
            'fecha_vencimiento' => fake()->date(),
            'importe_original' => fake()->randomFloat(2, 100, 10000),
            'saldo_actual' => fake()->randomFloat(2, 0, 10000),
            'plazo_dias' => 30,
            'estado_origen' => 'A',
            'fecha_carga' => fake()->date(),
        ];
    }
}
