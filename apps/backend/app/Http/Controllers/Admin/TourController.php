<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTourRequest;
use App\Http\Requests\Admin\UpdateTourRequest;
use App\Http\Resources\TourListResource;
use App\Http\Resources\TourResource;
use App\Models\Tour;
use App\Services\TourService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class TourController extends Controller
{
    public function __construct(
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
        $tour = $this->tourService->create($request->validated());

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
        $tour = $this->tourService->update($tour, $request->validated());

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
