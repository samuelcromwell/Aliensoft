<?php

namespace App\Policies;

use App\Models\TicketTier;
use App\Models\User;

class TicketTierPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('ticket-tiers.view-any');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TicketTier $ticketTier): bool
    {
        return $user->hasPermissionTo('ticket-tiers.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('ticket-tiers.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TicketTier $ticketTier): bool
    {
        return $user->hasPermissionTo('ticket-tiers.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TicketTier $ticketTier): bool
    {
        return $user->hasPermissionTo('ticket-tiers.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TicketTier $ticketTier): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TicketTier $ticketTier): bool
    {
        return false;
    }
}
