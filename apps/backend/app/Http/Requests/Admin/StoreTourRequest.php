<?php

namespace App\Http\Requests\Admin;

use App\Enums\TourCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTourRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('tours', 'slug')],
            'short_description' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'category' => ['required', Rule::in(TourCategory::values())],
            'is_active' => ['sometimes', 'boolean'],
            'main_image' => ['nullable', 'url', 'max:2048'],
            'main_image_file' => ['sometimes', 'file', 'image', 'max:5120'],
        ];
    }
}
