<?php

namespace App\Http\Requests\Admin;

use App\Enums\TourCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tourId = $this->route('tour')?->id;

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('tours', 'slug')->ignore($tourId)],
            'short_description' => ['sometimes', 'string', 'max:500'],
            'description' => ['sometimes', 'string'],
            'duration_days' => ['sometimes', 'integer', 'min:1'],
            'category' => ['sometimes', Rule::in(TourCategory::values())],
            'is_active' => ['sometimes', 'boolean'],
            'main_image' => ['sometimes', 'url', 'max:2048'],
        ];
    }
}
