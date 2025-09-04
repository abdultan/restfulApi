<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
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
            'seat' => [
                'id' => $this->seat->id,
                'label' => $this->seat->section . ' - ' . $this->seat->row . $this->seat->number,
                'price' => $this->seat->price,
            ],
            'event' => [
                'id' => $this->rezervation->event->id,
                'name' => $this->rezervation->event->name,
                'start_date' => $this->rezervation->event->start_date,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
