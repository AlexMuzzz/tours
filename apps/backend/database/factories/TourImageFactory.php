<?php

namespace Database\Factories;

use App\Models\Tour;
use App\Models\TourImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TourImage>
 */
class TourImageFactory extends Factory
{
    protected $model = TourImage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tour_id' => Tour::factory(),
            'image_url' => fake()->imageUrl(1280, 720, 'travel', true),
            'alt_text' => fake()->sentence(6),
            'sort_order' => fake()->numberBetween(0, 5),
        ];
    }
}
