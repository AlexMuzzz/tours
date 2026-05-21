<?php

namespace Database\Factories;

use App\Enums\CurrencyCode;
use App\Models\Tour;
use App\Models\TourDate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TourDate>
 */
class TourDateFactory extends Factory
{
    protected $model = TourDate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+2 weeks', '+6 months');
        $endDate = (clone $startDate)->modify('+'.fake()->numberBetween(2, 10).' days');

        return [
            'tour_id' => Tour::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'price' => fake()->randomFloat(2, 15000, 120000),
            'currency' => fake()->randomElement(CurrencyCode::values()),
            'available_seats' => fake()->numberBetween(0, 25),
        ];
    }
}
