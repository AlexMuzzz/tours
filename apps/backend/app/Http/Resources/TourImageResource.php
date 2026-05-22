<?php

namespace App\Http\Resources;

use App\Support\TourMediaPath;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TourImageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image_url' => TourMediaPath::publicUrl($this->image_url),
            'alt_text' => $this->alt_text,
            'sort_order' => $this->sort_order,
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
