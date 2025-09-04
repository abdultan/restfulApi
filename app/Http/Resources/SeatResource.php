<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SeatResource extends JsonResource
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
            'section' => $this->section,
            'row' => $this->row,
            'number' => $this->number,
            'status' => $this->status,
            'price' => $this->price,
            'venue_id' => $this->venue_id,
            'reserved_by' => $this->reserved_by,
            'reserved_until' => $this->reserved_until,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

