<?php

namespace App\Actions\TicketTiers;

use App\Models\TicketTier;

class PublishTicketTierAction
{
    public function execute(TicketTier $ticketTier): TicketTier
    {
        $ticketTier->forceFill([
            'is_published' => true,
        ])->save();

        return $ticketTier->refresh();
    }
}
