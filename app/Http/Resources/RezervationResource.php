<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RezervationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'expires_at' => $this->expires_at,
            'event' => [
                'id' => $this->event->id,
                'name' => $this->event->name,
                'start_date' => $this->event->start_date,
            ],
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'price' => $item->price,
                        'seat' => [
                            'id' => $item->seat->id,
                            'label' => $item->seat->section . ' - ' . $item->seat->row . $item->seat->number,
                            'price' => $item->seat->price,
                        ],
                    ];
                });
            }),
            'created_at' => $this->created_at,
        ];
    }
}
