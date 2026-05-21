<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTourImageRequest;
use App\Http\Resources\TourImageResource;
use App\Models\Tour;
use App\Models\TourImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TourImageController extends Controller
{
    /**
     * Добавить URL изображения к туру.
     *
     * Сохраняет внешний URL изображения, optional alt text и sort order для галереи.
     * Реальная загрузка файлов намеренно не входит в scope этого MVP.
     */
    public function store(StoreTourImageRequest $request, Tour $tour): JsonResponse
    {
        $image = $tour->images()->create($request->validated());

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
        $tourImage->delete();

        return response()->noContent();
    }
}
