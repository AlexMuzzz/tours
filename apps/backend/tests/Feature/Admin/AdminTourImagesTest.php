<?php

namespace Tests\Feature\Admin;

use App\Models\Tour;
use App\Models\TourImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminTourImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_add_tour_image(): void
    {
        $tour = Tour::factory()->create();

        $this->postJson("/api/admin/tours/{$tour->id}/images", [
            'image_url' => 'https://example.com/image.jpg',
        ])->assertUnauthorized();
    }

    public function test_invalid_tour_image_payload_returns_validation_error(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $tour = Tour::factory()->create();

        $response = $this->postJson("/api/admin/tours/{$tour->id}/images", [
            'image_url' => 'not-a-url',
            'sort_order' => -1,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'image_url',
                'sort_order',
            ]);
    }

    public function test_admin_can_delete_tour_image(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $image = TourImage::factory()->create();

        $this->deleteJson("/api/admin/tour-images/{$image->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tour_images', [
            'id' => $image->id,
        ]);
    }
}
