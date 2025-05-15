<?php

namespace App\Http\Requests;

use App\Enums\ConcertSource;
use App\Enums\ConcertStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateConcertRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'year' => 'sometimes|integer|min:1900|max:2100',
            'type' => 'sometimes|string|in:concert,festival,dj set,club show,theater show',
            'source' => 'sometimes|string|in:' . implode(',', array_column(ConcertSource::cases(), 'value')),
            'status' => 'sometimes|string|in:' . implode(',', array_column(ConcertStatus::cases(), 'value'))
        ];
    }
}
