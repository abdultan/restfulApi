<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SeatBlockRequest extends FormRequest
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
    
     public function prepareForValidation(): void
     {
         // 1) form-data / x-www-form-urlencoded / multipart
         $ids = $this->input('seat_ids');
     
         // 2) query string (?seat_ids[]=13&seat_ids[]=14)
         if ($ids === null) {
             $ids = $this->query('seat_ids');
         }
     
         // 3) raw JSON body (DELETE + application/json)
         if ($ids === null && str_starts_with((string)$this->header('Content-Type'), 'application/json')) {
             $raw = $this->getContent();
             if ($raw) {
                 $decoded = json_decode($raw, true);
                 if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && array_key_exists('seat_ids', $decoded)) {
                     $ids = $decoded['seat_ids'];
                 }
             }
         }
     
         // Dizi değilse diziye çevir
         if ($ids !== null && !is_array($ids)) {
             $ids = [$ids];
         }
     
         // Null ise boş dizi ver (rules 'required' bunu yakalayacak)
         $this->merge(['seat_ids' => $ids ?? []]);
     }
     
     public function rules()
    {
        return [
            'event_id' => ['required','integer','exists:events,id'],
            'seat_ids' =>['required','array','min:1','max:100'],
            'seat_ids.*'=> ['integer','distinct','exists:seats,id',],
        ];
    }
}
