<?php

namespace Database\Factories;

use App\Models\Concelho;
use App\Models\Distrito;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Concelho>
 */
class ConcelhoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'distrito_id' => Distrito::factory(),
            'nome' => fake()->unique()->city(),
        ];
    }
}
