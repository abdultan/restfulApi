<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketIndexRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:active,cancelled,transferred'],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
