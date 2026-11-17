<?php

namespace Database\Factories;

use App\Models\EmpresaApoiadora;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmpresaApoiadora>
 */
class EmpresaApoiadoraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->company(),
            'logo' => 'empresas/logo-fake.png',
            'ativo' => true,
            'ordem' => 0,
        ];
    }
}
