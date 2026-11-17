<?php

namespace Database\Factories;

use App\Models\Distrito;
use App\Models\PedidoOrcamento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PedidoOrcamento>
 */
class PedidoOrcamentoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(3),
            'nome' => fake()->name(),
            'telefone' => fake()->numerify('9########'),
            'email' => fake()->safeEmail(),
            'distrito_id' => Distrito::factory(),
            'descricao' => fake()->paragraph(),
            'termo_aceite' => true,
            'status' => 'validado',
        ];
    }
}
