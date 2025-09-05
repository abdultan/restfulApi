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
        $reservationItem = \App\Models\RezervationItem::where('rezervation_id', $this->rezervation_id)
            ->where('seat_id', $this->seat_id)
            ->first();
        
        $actualPrice = $reservationItem ? $reservationItem->price : $this->seat->price;
        
        return [
            'id' => $this->id,
            'status' => $this->status,
            'price' => $actualPrice, 
            'seat' => [
                'id' => $this->seat->id,
                'section' => $this->seat->section,
                'row' => $this->seat->row,
                'number' => $this->seat->number,
                'label' => $this->seat->section . ' - ' . $this->seat->row . $this->seat->number,
                'price' => $this->seat->price, // Original seat price
            ],
            'event' => [
                'id' => $this->rezervation->event->id,
                'name' => $this->rezervation->event->name,
                'start_date' => $this->rezervation->event->start_date,
                'venue' => [
                    'id' => $this->rezervation->event->venue->id,
                    'name' => $this->rezervation->event->venue->name,
                ],
            ],
            'created_at' => $this->created_at,
        ];
    }
}
