<?php

namespace Tests\Feature\Admin;

use App\Models\Tour;
use App\Models\TourDate;
use App\Models\TourImage;
use App\Models\TourRoutePoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminTourManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_create_tour(): void
    {
        $response = $this->postJson('/api/admin/tours', $this->tourPayload());

        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_list_tours(): void
    {
        $this->getJson('/api/admin/tours')->assertUnauthorized();
    }

    public function test_non_admin_user_cannot_create_tour(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/admin/tours', $this->tourPayload());

        $response->assertForbidden();
    }

    public function test_non_admin_user_cannot_list_tours(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/admin/tours')->assertForbidden();
    }

    public function test_admin_can_create_tour(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $response = $this->postJson('/api/admin/tours', $this->tourPayload([
            'title' => 'Kamchatka Expedition',
        ]));

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', 'Kamchatka Expedition')
            ->assertJsonPath('data.category', 'adventure');

        $tour = Tour::query()->where('title', 'Kamchatka Expedition')->firstOrFail();

        $this->assertDatabaseHas('tour_embeddings', [
            'tour_id' => $tour->id,
        ]);
    }

    public function test_admin_can_list_all_tours_including_inactive(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $activeTour = Tour::factory()->create(['is_active' => true]);
        $inactiveTour = Tour::factory()->inactive()->create();
        TourDate::factory()->create(['tour_id' => $activeTour->id]);
        TourDate::factory()->create(['tour_id' => $inactiveTour->id]);

        $response = $this->getJson('/api/admin/tours');

        $response->assertOk();

        $ids = array_column($response->json('data'), 'id');

        $this->assertContains($activeTour->id, $ids);
        $this->assertContains($inactiveTour->id, $ids);
    }

    public function test_admin_can_get_tour_by_id_with_relations(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $tour = Tour::factory()->inactive()->create();
        TourImage::factory()->count(2)->create(['tour_id' => $tour->id]);
        TourDate::factory()->count(2)->create(['tour_id' => $tour->id]);
        TourRoutePoint::factory()->count(3)->create(['tour_id' => $tour->id]);

        $response = $this->getJson("/api/admin/tours/{$tour->id}");

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $tour->id)
            ->assertJsonCount(2, 'data.images')
            ->assertJsonCount(2, 'data.dates')
            ->assertJsonCount(3, 'data.route_points');
    }

    public function test_admin_can_update_tour(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $tour = Tour::factory()->create([
            'title' => 'Old title',
            'slug' => 'old-title',
        ]);

        $response = $this->putJson("/api/admin/tours/{$tour->id}", [
            'title' => 'Updated title',
            'category' => 'culture',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated title')
            ->assertJsonPath('data.category', 'culture');

        $this->assertDatabaseHas('tours', [
            'id' => $tour->id,
            'title' => 'Updated title',
            'category' => 'culture',
        ]);
    }

    public function test_admin_can_update_tour_fields_and_regenerate_slug(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $tour = Tour::factory()->create([
            'title' => 'Northern Escape',
            'slug' => 'northern-escape',
        ]);

        $response = $this->putJson("/api/admin/tours/{$tour->id}", [
            'title' => 'Northern Escape Deluxe',
            'description' => 'Updated long-form description for the edited tour.',
            'duration_days' => 9,
            'category' => 'winter',
            'is_active' => false,
            'main_image' => 'https://example.com/updated-tour.jpg',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.title', 'Northern Escape Deluxe')
            ->assertJsonPath('data.slug', 'northern-escape-deluxe')
            ->assertJsonPath('data.description', 'Updated long-form description for the edited tour.')
            ->assertJsonPath('data.duration_days', 9)
            ->assertJsonPath('data.category', 'winter')
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('data.main_image', 'https://example.com/updated-tour.jpg');
    }

    public function test_invalid_tour_update_payload_returns_validation_error(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $tour = Tour::factory()->create();

        $response = $this->putJson("/api/admin/tours/{$tour->id}", [
            'duration_days' => 0,
            'category' => 'space',
            'main_image' => 'invalid-url',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'duration_days',
                'category',
                'main_image',
            ]);
    }

    public function test_admin_can_delete_tour(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $tour = Tour::factory()->create();

        $response = $this->deleteJson("/api/admin/tours/{$tour->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('tours', [
            'id' => $tour->id,
        ]);
    }

    public function test_deleted_tour_disappears_from_admin_and_public_endpoints(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $tour = Tour::factory()->create([
            'slug' => 'tour-to-delete',
            'is_active' => true,
        ]);
        TourDate::factory()->create(['tour_id' => $tour->id]);

        $this->deleteJson("/api/admin/tours/{$tour->id}")->assertNoContent();

        $this->getJson("/api/admin/tours/{$tour->id}")->assertNotFound();
        $this->getJson('/api/admin/tours')->assertOk()->assertJsonMissing(['id' => $tour->id]);
        $this->getJson('/api/tours')->assertOk()->assertJsonMissing(['id' => $tour->id]);
        $this->getJson('/api/tours/tour-to-delete')->assertNotFound();
    }

    public function test_admin_can_add_date_to_tour(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $tour = Tour::factory()->create();

        $response = $this->postJson("/api/admin/tours/{$tour->id}/dates", [
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
            'price' => 49999,
            'currency' => 'RUB',
            'available_seats' => 12,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.currency', 'RUB');

        $this->assertDatabaseHas('tour_dates', [
            'tour_id' => $tour->id,
            'currency' => 'RUB',
            'available_seats' => 12,
        ]);
    }

    public function test_admin_can_add_image_url_to_tour(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $tour = Tour::factory()->create();

        $response = $this->postJson("/api/admin/tours/{$tour->id}/images", [
            'image_url' => 'https://example.com/image.jpg',
            'alt_text' => 'Main image',
            'sort_order' => 1,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.image_url', 'https://example.com/image.jpg');

        $this->assertDatabaseHas('tour_images', [
            'tour_id' => $tour->id,
            'image_url' => 'https://example.com/image.jpg',
        ]);
    }

    public function test_admin_can_add_route_point_to_tour(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $tour = Tour::factory()->create();

        $response = $this->postJson("/api/admin/tours/{$tour->id}/route-points", [
            'title' => 'Summit',
            'description' => 'High point of the route',
            'latitude' => 43.123456,
            'longitude' => 42.654321,
            'sort_order' => 2,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', 'Summit');

        $this->assertDatabaseHas('tour_route_points', [
            'tour_id' => $tour->id,
            'title' => 'Summit',
        ]);
    }

    public function test_validation_error_is_returned_for_invalid_tour_payload(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $response = $this->postJson('/api/admin/tours', [
            'title' => '',
            'short_description' => '',
            'description' => '',
            'duration_days' => -1,
            'category' => 'invalid',
            'main_image' => 'not-a-url',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'title',
                'short_description',
                'description',
                'duration_days',
                'category',
                'main_image',
            ]);
    }

    public function test_generate_description_requires_authentication(): void
    {
        $tour = Tour::factory()->create();

        $this->postJson("/api/admin/tours/{$tour->id}/generate-description")
            ->assertUnauthorized();
    }

    public function test_non_admin_user_cannot_generate_description(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $tour = Tour::factory()->create();

        $this->postJson("/api/admin/tours/{$tour->id}/generate-description")
            ->assertForbidden();
    }

    public function test_admin_can_generate_description_stub(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $tour = Tour::factory()->create([
            'title' => 'Kamchatka Explorer',
            'category' => 'adventure',
            'duration_days' => 8,
        ]);

        $response = $this->postJson("/api/admin/tours/{$tour->id}/generate-description");

        $response
            ->assertOk()
            ->assertJsonPath(
                'description',
                '«Kamchatka Explorer» — это приключенческий тур на 8 дней с насыщенным, но комфортным ритмом. Маршрут собран так, чтобы показать ключевые впечатления региона, оставить время на спокойные прогулки и дать путешественнику цельную историю поездки без перегруза переездами.'
            );
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function tourPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Altai Explorer',
            'short_description' => 'A concise but vivid tour description.',
            'description' => 'A full description for the tour that is long enough for the MVP backend.',
            'duration_days' => 6,
            'category' => 'adventure',
            'is_active' => true,
            'main_image' => 'https://example.com/tour.jpg',
        ], $overrides);
    }
}
