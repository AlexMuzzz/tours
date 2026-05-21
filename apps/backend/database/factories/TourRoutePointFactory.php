<?php

namespace Database\Factories;

use App\Models\Tour;
use App\Models\TourRoutePoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TourRoutePoint>
 */
class TourRoutePointFactory extends Factory
{
    protected $model = TourRoutePoint::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tour_id' => Tour::factory(),
            'title' => fake()->city(),
            'description' => fake()->sentence(12),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'sort_order' => fake()->numberBetween(0, 5),
        ];
    }
}
