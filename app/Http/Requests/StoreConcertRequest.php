<?php

namespace App\Http\Requests;

use App\Enums\ConcertSource;
use App\Enums\ConcertStatus;
use Illuminate\Foundation\Http\FormRequest;

class StoreConcertRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'year' => 'required|integer|min:1900|max:2100',
            'type' => 'required|string|in:concert,festival,dj set,club show,theater show',
            'source' => 'required|string|in:' . implode(',', array_column(ConcertSource::cases(), 'value')),
            'status' => 'required|string|in:' . implode(',', array_column(ConcertStatus::cases(), 'value')),
            'date' => 'required|date',
            'location_id' => 'required|exists:locations,id'
        ];
    }
}
