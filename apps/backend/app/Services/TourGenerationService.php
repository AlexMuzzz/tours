<?php

namespace App\Services;

use App\Enums\TourCategory;
use App\Models\Tour;

class TourGenerationService
{
    public function generateDescription(Tour $tour): string
    {
        $category = $tour->category instanceof TourCategory
            ? mb_strtolower($tour->category->label())
            : 'авторский';

        // TODO: Replace this deterministic stub with an LLM provider integration.
        return sprintf(
            '«%s» — это %s тур на %d дней с насыщенным, но комфортным ритмом. Маршрут собран так, чтобы показать ключевые впечатления региона, оставить время на спокойные прогулки и дать путешественнику цельную историю поездки без перегруза переездами.',
            $tour->title,
            $category,
            $tour->duration_days,
        );
    }
}
