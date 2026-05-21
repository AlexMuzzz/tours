<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Services\TourGenerationService;
use Illuminate\Http\JsonResponse;

class TourGenerationController extends Controller
{
    public function __construct(
        private readonly TourGenerationService $tourGenerationService,
    ) {
    }

    /**
     * Сгенерировать stub-описание для тура.
     *
     * Использует текущие title, category и duration для построения детерминированного
     * placeholder-описания. Endpoint подготовлен для будущей интеграции с LLM.
     */
    public function __invoke(Tour $tour): JsonResponse
    {
        return response()->json([
            'description' => $this->tourGenerationService->generateDescription($tour),
        ]);
    }
}
