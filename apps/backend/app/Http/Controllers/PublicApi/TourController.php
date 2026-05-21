<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicApi\ListToursRequest;
use App\Http\Resources\TourListResource;
use App\Http\Resources\TourResource;
use App\Models\Tour;
use App\Services\TourCatalogQueryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TourController extends Controller
{
    public function __construct(
        private readonly TourCatalogQueryService $tourCatalogQueryService,
    ) {
    }

    /**
     * Вернуть пагинированный список активных туров для публичного каталога.
     *
     * Поддерживает фильтрацию по category, duration и price, а также текстовый поиск,
     * сортировку и пагинацию для публичной страницы каталога.
     */
    public function index(ListToursRequest $request): AnonymousResourceCollection
    {
        $tours = $this->tourCatalogQueryService->paginate($request->validated());

        return TourListResource::collection($tours);
    }

    /**
     * Вернуть один активный тур по slug для публичного сайта.
     *
     * Загружает изображения галереи, даты выездов и упорядоченные route points, чтобы
     * публичная страница тура могла быть собрана одним API-запросом.
     */
    public function show(string $slug): TourResource
    {
        $tour = Tour::query()
            ->active()
            ->with(['images', 'dates', 'routePoints'])
            ->withMin('dates', 'price')
            ->where('slug', $slug)
            ->firstOrFail();

        return TourResource::make($tour);
    }
}
