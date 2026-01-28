<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use App\Settings\TicketSettings;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // Handle guest tickets (no owner)
        if (!$ticket->owner_id) {
            // Only admins can view guest tickets
            return $user->hasAnyRole(['Super Admin', 'Global Viewer', 'Admin Unit']);
        }

        // Display all tickets to Super Admin and Global Viewer
        if ($user->hasAnyRole(['Super Admin', 'Global Viewer'])) {
            return true;
        }

        // The Admin Unit/Unit Viewer can view tickets that are assigned to their specific unit.
        if ($user->hasAnyRole(['Admin Unit', 'Unit Viewer'])) {
            return $user->id == $ticket->owner_id || $ticket->unit_id == $user->unit_id;
        }

        // The staff unit can view tickets that have been assigned to them.
        if ($user->hasRole('Staff Unit')) {
            return $user->id == $ticket->owner_id || $ticket->responsible_id == $user->id;
        }

        // The user can view their own ticket
        return $user->id == $ticket->owner_id;
    }

    /**
     * Determine whether the user can create models.
     * Allow public ticket creation (no auth required).
     */
    public function create(?User $user): bool
    {
        return true; // Public access allowed
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // ticket closed
        if (in_array($ticket->ticketStatus->id, app(TicketSettings::class)->closed_status)) {
            return false;
        }

        // The admin unit can update tickets that are assigned to their specific unit.
        if ($user->hasRole('Admin Unit')) {
            return $user->id == $ticket->owner_id || $ticket->unit_id == $user->unit_id;
        }

        // The staff unit can update tickets that have been assigned to them.
        if ($user->hasRole('Staff Unit')) {
            return $user->id == $ticket->owner_id || $ticket->responsible_id == $user->id;
        }

        // The user can view their own ticket
        return $user->id == $ticket->owner_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return $user->id == $ticket->owner_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return $user->id == $ticket->owner_id;
    }
}
