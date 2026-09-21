<?php

namespace Database\Factories;

use App\Models\EmpresaMandante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmpresaMandante>
 */
class EmpresaMandanteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->lexify('EMP-????'),
            'razon_social' => fake()->company(),
            'activo' => true,
        ];
    }
}
