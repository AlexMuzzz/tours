<?php

namespace App\Http\Requests\PublicApi;

use App\Enums\TourCategory;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ListToursRequest extends FormRequest
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
            'category' => ['nullable', Rule::in(TourCategory::values())],
            'duration_min' => ['nullable', 'integer', 'min:1'],
            'duration_max' => ['nullable', 'integer', 'min:1'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', Rule::in(['newest', 'price_asc', 'price_desc', 'duration_asc', 'duration_desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $durationMin = $this->integer('duration_min');
                $durationMax = $this->integer('duration_max');
                $priceMin = $this->input('price_min');
                $priceMax = $this->input('price_max');

                if ($this->filled('duration_min') && $this->filled('duration_max') && $durationMin > $durationMax) {
                    $validator->errors()->add('duration_max', 'The duration_max field must be greater than or equal to duration_min.');
                }

                if ($this->filled('price_min') && $this->filled('price_max') && (float) $priceMin > (float) $priceMax) {
                    $validator->errors()->add('price_max', 'The price_max field must be greater than or equal to price_min.');
                }
            },
        ];
    }
}
