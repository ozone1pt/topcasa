<?php

namespace Database\Factories;

use App\Models\Anuncio;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'anuncio_id' => Anuncio::factory(),
            'user_id' => User::factory(),
            'anunciante_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->sentence(),
            'status' => 'pendente',
        ];
    }
}
