<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketTransferRequest extends FormRequest
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
            'email' => ['required', 'email', 'exists:users,email'],
            'note'  => ['nullable', 'string', 'max:500'],
        ];
    }
    public function messages(): array
    {
        return [
            'email.exists' => 'Bu email adresiyle kayıtlı kullanıcı bulunamadı.',
        ];
    }
}
