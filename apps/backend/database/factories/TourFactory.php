<?php

namespace Database\Factories;

use App\Enums\TourCategory;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tour>
 */
class TourFactory extends Factory
{
    protected $model = Tour::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 999),
            'short_description' => fake()->sentence(12),
            'description' => fake()->paragraphs(3, true),
            'duration_days' => fake()->numberBetween(2, 14),
            'category' => fake()->randomElement(TourCategory::values()),
            'is_active' => true,
            'main_image' => fake()->imageUrl(1280, 720, 'travel', true),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
