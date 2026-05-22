<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTourRequest;
use App\Http\Requests\Admin\UpdateTourRequest;
use App\Http\Resources\TourListResource;
use App\Http\Resources\TourResource;
use App\Models\Tour;
use App\Services\TourMediaService;
use App\Services\TourService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class TourController extends Controller
{
    public function __construct(
        private readonly TourMediaService $tourMediaService,
        private readonly TourService $tourService,
    ) {
    }

    /**
     * Вернуть пагинированный список всех туров для админки.
     *
     * Включает и активные, и неактивные туры, чтобы администратор мог управлять
     * черновиками, скрытыми и опубликованными записями из одного endpoint-а.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max((int) $request->integer('per_page', 20), 1), 50);

        $tours = Tour::query()
            ->withMin('dates', 'price')
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return TourListResource::collection($tours);
    }

    /**
     * Создать новый тур в админском каталоге.
     *
     * Генерирует уникальный slug, сохраняет основные поля тура и обновляет stub-запись
     * embeddings, подготовленную для будущего semantic search.
     */
    public function store(StoreTourRequest $request): JsonResponse
    {
        $attributes = $request->safe()->except(['main_image_file']);
        $hasUploadedMainImage = $request->hasFile('main_image_file');

        if ($hasUploadedMainImage) {
            unset($attributes['main_image']);
        }

        $tour = $this->tourService->create($attributes);

        if ($hasUploadedMainImage) {
            $tour = $this->tourService->setMainImage(
                $tour,
                $this->tourMediaService->storeMainImage($tour, $request->file('main_image_file'))
            );
        }

        return TourResource::make($tour->loadMin('dates', 'price'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Вернуть один тур по внутреннему ID для редактирования в админке.
     *
     * Загружает связанные изображения, даты, route points и embedding-метаданные, чтобы
     * frontend мог получить полную форму редактирования одним запросом.
     */
    public function show(Tour $tour): TourResource
    {
        return TourResource::make($tour->load(['images', 'dates', 'routePoints', 'embedding'])->loadMin('dates', 'price'));
    }

    /**
     * Обновить существующий тур в админском каталоге.
     *
     * Поддерживает частичное обновление основных полей тура и пересоздаёт slug и
     * embedding stub, если изменились исходные данные.
     */
    public function update(UpdateTourRequest $request, Tour $tour): TourResource
    {
        $previousMainImage = $tour->getRawOriginal('main_image');
        $attributes = $request->safe()->except(['main_image_file', 'remove_main_image']);
        $hasUploadedMainImage = $request->hasFile('main_image_file');
        $shouldRemoveMainImage = $request->boolean('remove_main_image') && ! $hasUploadedMainImage;

        if ($hasUploadedMainImage) {
            unset($attributes['main_image']);
        }

        if ($shouldRemoveMainImage) {
            $attributes['main_image'] = null;
        }

        $tour = $this->tourService->update($tour, $attributes);

        if ($hasUploadedMainImage) {
            $this->tourMediaService->delete($previousMainImage);

            $tour = $this->tourService->setMainImage(
                $tour,
                $this->tourMediaService->storeMainImage($tour, $request->file('main_image_file'))
            );
        } elseif (array_key_exists('main_image', $attributes) && $attributes['main_image'] !== $previousMainImage) {
            $this->tourMediaService->delete($previousMainImage);
        }

        return TourResource::make($tour->loadMin('dates', 'price'));
    }

    /**
     * Удалить тур из каталога.
     *
     * Связанные даты, изображения, route points и embedding-записи удаляются через
     * каскадные правила базы данных.
     */
    public function destroy(Tour $tour): Response
    {
        $this->tourService->delete($tour);

        return response()->noContent();
    }
}
