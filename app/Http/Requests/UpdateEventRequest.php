<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use App\Models\Event;

class UpdateEventRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'venue_id' => ['sometimes', 'required', 'exists:venues,id'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date'],
            'status' => ['sometimes', 'nullable', 'in:draft,published,cancelled,archived'],
        ];
    }

    public function withValidator($validator){
        $validator->after(function ($v) {
            /** @var Event $event */
            $event = $this->route('event');

            if (!$event) {
                return;
            }

            if ($this->filled('venue_id') && $event->status === 'published' && $this->venue_id != $event->venue_id) {
                $v->errors()->add('venue_id', 'Published etkinlikte venue değiştirilemez.');
            }

            if ($this->filled('start_date') && now()->gt(new Carbon($this->start_date))) {
                $v->errors()->add('start_date', 'Start geçmiş bir zamana çekilemez.');
            }

            if ($this->filled('status')) {
                $from = $event->status;
                $to   = $this->status;
                $illegal = $from === 'archived' && $to === 'published';
                if ($illegal) {
                    $v->errors()->add('status', 'Archived -> Published geçişine izin yok.');
                }
            }
        });
    }
}
