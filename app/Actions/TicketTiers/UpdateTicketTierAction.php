<?php

namespace App\Actions\TicketTiers;

use App\Data\TicketTiers\UpdateTicketTierData;
use App\Models\TicketTier;
use Spatie\LaravelData\Optional;

class UpdateTicketTierAction
{
    public function execute(TicketTier $ticketTier, UpdateTicketTierData $data): TicketTier
    {
        $ticketTier->fill($this->attributes($data));
        $ticketTier->save();

        return $ticketTier->refresh();
    }

    private function attributes(UpdateTicketTierData $data): array
    {
        return collect([
            'event_id' => $data->event_id,
            'name' => $data->name,
            'price' => $data->price,
            'quantity' => $data->quantity,
            'sales_channels' => $data->sales_channels,
            'is_active' => $data->is_active,
        ])->reject(fn (mixed $value): bool => $value instanceof Optional)->all();
    }
}
