<?php

namespace Tests\Feature\Admin;

use App\Models\Tour;
use App\Models\TourDate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminTourDatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_tour_date_payload_returns_validation_error_on_create(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $tour = Tour::factory()->create();

        $response = $this->postJson("/api/admin/tours/{$tour->id}/dates", [
            'start_date' => '2026-07-10',
            'end_date' => '2026-07-05',
            'price' => -10,
            'currency' => 'GBP',
            'available_seats' => -1,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'end_date',
                'price',
                'currency',
                'available_seats',
            ]);
    }

    public function test_admin_can_update_tour_date(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $tourDate = TourDate::factory()->create([
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
            'price' => 40000,
            'currency' => 'RUB',
            'available_seats' => 10,
        ]);

        $response = $this->putJson("/api/admin/tour-dates/{$tourDate->id}", [
            'start_date' => '2026-06-03',
            'end_date' => '2026-06-08',
            'price' => 45500,
            'currency' => 'USD',
            'available_seats' => 7,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.start_date', '2026-06-03')
            ->assertJsonPath('data.end_date', '2026-06-08')
            ->assertJsonPath('data.price', 45500)
            ->assertJsonPath('data.currency', 'USD')
            ->assertJsonPath('data.available_seats', 7);
    }

    public function test_invalid_tour_date_payload_returns_validation_error_on_update(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $tourDate = TourDate::factory()->create([
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
        ]);

        $response = $this->putJson("/api/admin/tour-dates/{$tourDate->id}", [
            'start_date' => '2026-06-09',
            'end_date' => '2026-06-08',
            'price' => -1,
            'currency' => 'GBP',
            'available_seats' => -2,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'end_date',
                'price',
                'currency',
                'available_seats',
            ]);
    }

    public function test_admin_can_delete_tour_date(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $tourDate = TourDate::factory()->create();

        $this->deleteJson("/api/admin/tour-dates/{$tourDate->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tour_dates', [
            'id' => $tourDate->id,
        ]);
    }
}
