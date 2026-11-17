<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\GlossarioTermo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GlossarioTermo>
 */
class GlossarioTermoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'categoria_id' => Categoria::factory(),
            'termo' => fake()->unique()->word(),
            'definicao' => fake()->sentence(),
        ];
    }
}
