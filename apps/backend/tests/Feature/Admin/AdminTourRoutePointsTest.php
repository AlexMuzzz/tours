<?php

namespace Tests\Feature\Admin;

use App\Models\Tour;
use App\Models\TourRoutePoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminTourRoutePointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_tour_route_point_payload_returns_validation_error(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $tour = Tour::factory()->create();

        $response = $this->postJson("/api/admin/tours/{$tour->id}/route-points", [
            'title' => '',
            'latitude' => 120,
            'longitude' => 220,
            'sort_order' => -1,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'title',
                'latitude',
                'longitude',
                'sort_order',
            ]);
    }

    public function test_admin_can_update_tour_route_point(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $routePoint = TourRoutePoint::factory()->create([
            'title' => 'Old point',
            'description' => 'Old description',
            'latitude' => 45.1,
            'longitude' => 38.9,
            'sort_order' => 1,
        ]);

        $response = $this->putJson("/api/admin/tour-route-points/{$routePoint->id}", [
            'title' => 'New point',
            'description' => 'New description',
            'latitude' => 46.2,
            'longitude' => 39.8,
            'sort_order' => 3,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.title', 'New point')
            ->assertJsonPath('data.description', 'New description')
            ->assertJsonPath('data.latitude', 46.2)
            ->assertJsonPath('data.longitude', 39.8)
            ->assertJsonPath('data.sort_order', 3);
    }

    public function test_admin_can_delete_tour_route_point(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $routePoint = TourRoutePoint::factory()->create();

        $this->deleteJson("/api/admin/tour-route-points/{$routePoint->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tour_route_points', [
            'id' => $routePoint->id,
        ]);
    }
}
