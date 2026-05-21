<?php

namespace App\Http\Requests\Admin;

use Closure;
use App\Enums\CurrencyCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateTourDateRequest extends FormRequest
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
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', Rule::in(CurrencyCode::values())],
            'available_seats' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $tourDate = $this->route('tourDate');
                $startDate = $this->input('start_date', optional($tourDate?->start_date)->toDateString());
                $endDate = $this->input('end_date', optional($tourDate?->end_date)->toDateString());

                if ($startDate !== null && $endDate !== null && $startDate > $endDate) {
                    $validator->errors()->add('end_date', 'The end_date field must be a date after or equal to start_date.');
                }
            },
        ];
    }
}
