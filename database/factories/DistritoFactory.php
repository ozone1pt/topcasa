<?php

namespace Database\Factories;

use App\Models\Distrito;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Distrito>
 */
class DistritoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->unique()->city(),
        ];
    }
}
