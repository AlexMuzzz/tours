<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTourRoutePointRequest;
use App\Http\Requests\Admin\UpdateTourRoutePointRequest;
use App\Http\Resources\TourRoutePointResource;
use App\Models\Tour;
use App\Models\TourRoutePoint;
use App\Services\TourService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TourRoutePointController extends Controller
{
    public function __construct(
        private readonly TourService $tourService,
    ) {
    }

    /**
     * Добавить точку маршрута в itinerary тура.
     *
     * Сохраняет title, optional description, координаты и sort order, чтобы frontend
     * позже мог отрисовать упорядоченный маршрут и маркеры на карте.
     */
    public function store(StoreTourRoutePointRequest $request, Tour $tour): JsonResponse
    {
        $routePoint = $tour->routePoints()->create($request->validated());
        $this->tourService->refreshEmbedding($tour);

        return TourRoutePointResource::make($routePoint)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Обновить точку маршрута внутри itinerary тура.
     *
     * Позволяет менять текстовое содержимое, координаты и порядок отображения одной
     * точки без изменения остального маршрута.
     */
    public function update(UpdateTourRoutePointRequest $request, TourRoutePoint $tourRoutePoint): TourRoutePointResource
    {
        $tourRoutePoint->update($request->validated());
        $this->tourService->refreshEmbedding($tourRoutePoint->tour()->firstOrFail());

        return TourRoutePointResource::make($tourRoutePoint->fresh());
    }

    /**
     * Удалить точку маршрута из itinerary тура.
     *
     * Удаляет одну упорядоченную остановку из маршрута, который frontend может
     * использовать для таймлайна и карты.
     */
    public function destroy(TourRoutePoint $tourRoutePoint): Response
    {
        $tour = $tourRoutePoint->tour()->firstOrFail();
        $tourRoutePoint->delete();
        $this->tourService->refreshEmbedding($tour);

        return response()->noContent();
    }
}
