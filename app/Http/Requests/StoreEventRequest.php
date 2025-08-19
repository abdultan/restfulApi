<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
<<<<<<< HEAD
        return false;
=======
        return true;
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
<<<<<<< HEAD
            //
=======
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'venue_id' => ['required', 'exists:venues,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['nullable', 'in:draft,published,cancelled,archived'],
>>>>>>> 6291303 (ticket ve event işlemleri yapıldı)
        ];
    }
}
