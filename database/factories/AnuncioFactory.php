<?php

namespace Database\Factories;

use App\Models\Anuncio;
use App\Models\Categoria;
use App\Models\Concelho;
use App\Models\Distrito;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Anuncio>
 */
class AnuncioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'titulo' => fake()->sentence(4),
            'descricao' => fake()->paragraph(),
            'email' => fake()->safeEmail(),
            'contacto' => fake()->numerify('9########'),
            'categoria_id' => Categoria::factory(),
            'distrito_id' => Distrito::factory(),
            'concelho_id' => Concelho::factory(),
            'status' => 'aprovado',
        ];
    }
}
