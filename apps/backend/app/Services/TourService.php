<?php

namespace App\Services;

use App\Models\Tour;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class TourService
{
    public function __construct(
        private readonly EmbeddingService $embeddingService,
        private readonly TourMediaService $tourMediaService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Tour
    {
        $attributes['slug'] = $this->resolveSlug($attributes);

        $tour = Tour::query()->create($attributes);

        return $this->refreshEmbedding($tour);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Tour $tour, array $attributes): Tour
    {
        $attributes['slug'] = $this->resolveSlug($attributes, $tour);

        $tour->fill($attributes);
        $tour->save();

        return $this->refreshEmbedding($tour);
    }

    public function delete(Tour $tour): void
    {
        $tour->loadMissing('images');

        $this->tourMediaService->delete($tour->getRawOriginal('main_image'));

        foreach ($tour->images as $image) {
            $this->tourMediaService->delete($image->getRawOriginal('image_url'));
        }

        $tour->delete();

        $this->tourMediaService->deleteTourDirectory($tour);
    }

    public function setMainImage(Tour $tour, ?string $mainImage): Tour
    {
        $tour->forceFill([
            'main_image' => $mainImage,
        ])->save();

        return $this->reloadTour($tour);
    }

    public function refreshEmbedding(Tour $tour): Tour
    {
        $tour->loadMissing(['images', 'dates', 'routePoints', 'embedding']);

        $sourceText = $this->embeddingService->buildSourceText($tour);
        $embedding = $this->embeddingService->generateForTour($tour);

        $payload = [
            'source_text' => $sourceText,
        ];

        if ($embedding !== null || $tour->embedding === null) {
            $payload['embedding'] = $embedding;
        }

        $tour->embedding()->updateOrCreate(
            ['tour_id' => $tour->id],
            $payload
        );

        return $this->reloadTour($tour);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function resolveSlug(array $attributes, ?Tour $tour = null): string
    {
        if ($tour !== null && ! array_key_exists('slug', $attributes) && ! array_key_exists('title', $attributes)) {
            return $tour->slug;
        }

        $source = Arr::get($attributes, 'slug') ?: Arr::get($attributes, 'title') ?: $tour?->title ?: 'tour';
        $baseSlug = Str::slug((string) $source);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'tour';
        $candidate = $baseSlug;
        $counter = 2;

        while (
            Tour::query()
                ->when($tour !== null, fn ($query) => $query->whereKeyNot($tour->id))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $candidate;
    }

    private function reloadTour(Tour $tour): Tour
    {
        return $tour->fresh(['images', 'dates', 'routePoints', 'embedding']);
    }
}
