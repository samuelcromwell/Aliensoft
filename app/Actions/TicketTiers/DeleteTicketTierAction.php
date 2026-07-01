<?php

namespace App\Actions\TicketTiers;

use App\Models\TicketTier;

class DeleteTicketTierAction
{
    public function execute(TicketTier $ticketTier): void
    {
        $ticketTier->delete();
    }
}
