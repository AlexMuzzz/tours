<?php

namespace Database\Factories;

use App\Models\Tour;
use App\Models\TourEmbedding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TourEmbedding>
 */
class TourEmbeddingFactory extends Factory
{
    protected $model = TourEmbedding::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tour_id' => Tour::factory(),
            'embedding' => [0.12, 0.35, 0.48, 0.77, 0.91, 0.23, 0.56, 0.14],
            'source_text' => fake()->paragraph(),
        ];
    }
}
