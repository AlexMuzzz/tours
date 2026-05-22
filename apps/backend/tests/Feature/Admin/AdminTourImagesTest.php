<?php

namespace Tests\Feature\Admin;

use App\Models\Tour;
use App\Models\TourImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_admin_can_add_uploaded_tour_image(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->admin()->create());
        $tour = Tour::factory()->create();

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->post("/api/admin/tours/{$tour->id}/images", [
                'image_file' => $this->uploadedPng('gallery.png'),
                'alt_text' => 'Gallery image',
                'sort_order' => 3,
            ]);

        $image = TourImage::query()->where('tour_id', $tour->id)->firstOrFail();
        $storedPath = $image->getRawOriginal('image_url');

        $this->assertNotNull($storedPath);
        Storage::disk('public')->assertExists($storedPath);

        $response
            ->assertCreated()
            ->assertJsonPath('data.alt_text', 'Gallery image')
            ->assertJsonPath('data.sort_order', 3)
            ->assertJsonPath('data.image_url', Storage::disk('public')->url($storedPath));
    }

    public function test_admin_can_delete_uploaded_tour_image_and_remove_file(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->admin()->create());

        $tour = Tour::factory()->create();
        $storedPath = "tours/{$tour->id}/gallery/delete-me.jpg";
        $image = TourImage::factory()->create([
            'tour_id' => $tour->id,
            'image_url' => $storedPath,
        ]);

        Storage::disk('public')->put($storedPath, 'image');

        $this->deleteJson("/api/admin/tour-images/{$image->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tour_images', [
            'id' => $image->id,
        ]);
        Storage::disk('public')->assertMissing($storedPath);
    }

    private function uploadedPng(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9s6nxsQAAAAASUVORK5CYII=')
        );
    }
}
