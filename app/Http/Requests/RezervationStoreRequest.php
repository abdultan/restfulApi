<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RezervationStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'event_id' => ['required','integer','exists:events,id'],
            'seat_ids' => ['required','array','min:1'],
            'seat_ids.*' => ['integer','distinct','exists:seats,id'],
        ];
    }
}
