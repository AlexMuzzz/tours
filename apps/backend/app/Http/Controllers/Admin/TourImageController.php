<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTourImageRequest;
use App\Http\Resources\TourImageResource;
use App\Models\Tour;
use App\Models\TourImage;
use App\Services\TourMediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TourImageController extends Controller
{
    public function __construct(
        private readonly TourMediaService $tourMediaService,
    ) {
    }

    /**
     * Добавить URL изображения к туру.
     *
     * Сохраняет внешний URL изображения, optional alt text и sort order для галереи.
     * Реальная загрузка файлов намеренно не входит в scope этого MVP.
     */
    public function store(StoreTourImageRequest $request, Tour $tour): JsonResponse
    {
        $attributes = $request->safe()->except(['image_file']);

        if ($request->hasFile('image_file')) {
            $attributes['image_url'] = $this->tourMediaService->storeGalleryImage($tour, $request->file('image_file'));
        }

        $image = $tour->images()->create($attributes);

        return TourImageResource::make($image)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Удалить одно изображение тура.
     *
     * Удаляет только запись галереи и не управляет удалёнными файлами, потому что в
     * этом MVP изображения хранятся как обычные URL.
     */
    public function destroy(TourImage $tourImage): Response
    {
        $this->tourMediaService->delete($tourImage->getRawOriginal('image_url'));
        $tourImage->delete();

        return response()->noContent();
    }
}
