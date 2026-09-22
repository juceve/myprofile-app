<?php

namespace Database\Factories;

use App\Models\EmpresaMandante;
use App\Models\JefeVenta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JefeVenta>
 */
class JefeVentaFactory extends Factory
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
            'nombre' => fake()->name(),
        ];
    }
}
