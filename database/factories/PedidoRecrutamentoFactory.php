<?php

namespace Database\Factories;

use App\Models\Distrito;
use App\Models\PedidoRecrutamento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PedidoRecrutamento>
 */
class PedidoRecrutamentoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => fake()->jobTitle(),
            'empresa_nome' => fake()->company(),
            'email' => fake()->safeEmail(),
            'telefone' => fake()->numerify('9########'),
            'distrito_id' => Distrito::factory(),
            'descricao' => fake()->paragraph(),
            'termo_aceite' => true,
            'status' => 'ativo',
        ];
    }
}
