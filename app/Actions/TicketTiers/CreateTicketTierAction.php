<?php

namespace App\Actions\TicketTiers;

use App\Data\TicketTiers\CreateTicketTierData;
use App\Models\TicketTier;
use Spatie\LaravelData\Optional;

class CreateTicketTierAction
{
    public function execute(CreateTicketTierData $data): TicketTier
    {
        $ticketTier = TicketTier::create([
            'event_id' => $data->event_id,
            'name' => $data->name,
            'price' => $data->price,
            'quantity' => $data->quantity,
            'sales_channels' => $this->optionalValue($data->sales_channels),
            'is_active' => $this->optionalValue($data->is_active, true),
        ]);

        return $ticketTier->refresh();
    }

    private function optionalValue(mixed $value, mixed $default = null): mixed
    {
        return $value instanceof Optional ? $default : $value;
    }
}
