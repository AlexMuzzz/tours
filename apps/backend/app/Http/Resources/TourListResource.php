<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TourListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'duration_days' => $this->duration_days,
            'category' => optional($this->category)->value ?? $this->category,
            'is_active' => (bool) $this->is_active,
            'main_image' => $this->main_image,
            'price_from' => $this->dates_min_price !== null ? (float) $this->dates_min_price : null,
            'score' => isset($this->score) ? (float) $this->score : null,
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
