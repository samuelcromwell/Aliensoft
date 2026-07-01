<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketTierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_id' => $this->whenHas('event_id'),
            'name' => $this->whenHas('name'),
            'price' => $this->whenHas('price'),
            'quantity' => $this->whenHas('quantity'),
            'sales_channels' => $this->whenHas('sales_channels'),
            'is_published' => $this->whenHas('is_published'),
            'is_active' => $this->whenHas('is_active'),
            'created_at' => $this->whenHas('created_at'),
            'updated_at' => $this->whenHas('updated_at'),
            'deleted_at' => $this->whenHas('deleted_at'),
            'event' => EventResource::make($this->whenLoaded('event')),
        ];
    }
}
