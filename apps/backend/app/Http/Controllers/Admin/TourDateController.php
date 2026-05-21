<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTourDateRequest;
use App\Http\Requests\Admin\UpdateTourDateRequest;
use App\Http\Resources\TourDateResource;
use App\Models\Tour;
use App\Models\TourDate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TourDateController extends Controller
{
    /**
     * Добавить дату выезда и ценовой вариант к туру.
     *
     * Каждая запись даты хранит интервал поездки, цену, валюту и доступные места, чтобы
     * каталог мог показывать несколько вариантов бронирования одного тура.
     */
    public function store(StoreTourDateRequest $request, Tour $tour): JsonResponse
    {
        $tourDate = $tour->dates()->create($request->validated());

        return TourDateResource::make($tourDate)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Обновить существующую дату выезда у тура.
     *
     * Поддерживает частичное редактирование дат поездки, цены, валюты и количества мест
     * для одного расписанного варианта тура.
     */
    public function update(UpdateTourDateRequest $request, TourDate $tourDate): TourDateResource
    {
        $tourDate->update($request->validated());

        return TourDateResource::make($tourDate->fresh());
    }

    /**
     * Удалить дату выезда у тура.
     *
     * Полезно, когда заезд отменён, окончательно закрыт для продаж или должен быть
     * создан заново с другими условиями.
     */
    public function destroy(TourDate $tourDate): Response
    {
        $tourDate->delete();

        return response()->noContent();
    }
}
