<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCreated;
use App\Notifications\TicketDeleted;
use App\Notifications\TicketRestored;
use App\Notifications\TicketStatusUpdated;
use App\Settings\AccountSettings;
use Illuminate\Support\Collection;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        $authUser = auth()->user();
        $notifiedUsers = collect([$authUser?->id])->filter(); // Track users we've notified to avoid duplicates
        
        // Eager load ticket owner to avoid N+1 queries
        $ticket->loadMissing('owner');

        // Notify ticket owner (if exists and not the creator)
        if ($ticket->owner && $ticket->owner->id !== $authUser?->id) {
            try {
                \Log::info('Sending ticket created notification to ticket owner', [
                    'ticket_id' => $ticket->id,
                    'owner_id' => $ticket->owner->id,
                    'owner_email' => $ticket->owner->email,
                ]);
                
                $ticket->owner->notify(new TicketCreated($ticket));
                $notifiedUsers->push($ticket->owner->id);
            } catch (\Exception $e) {
                \Log::error('Failed to send ticket created notification to ticket owner', [
                    'ticket_id' => $ticket->id,
                    'owner_id' => $ticket->owner->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Notify Super Admin users
        try {
            $superAdmins = User::role('Super Admin')
                ->whereNotNull('email')
                ->when($accountSettings = app(AccountSettings::class)->user_email_verification, function ($query) {
                    $query->whereNotNull('email_verified_at');
                })
                ->where('id', '!=', $authUser?->id)
                ->get();
            
            $superAdmins->each(function ($user) use ($ticket, &$notifiedUsers) {
                if (!$notifiedUsers->contains($user->id)) {
                    try {
                        \Log::info('Sending ticket created notification to Super Admin', [
                            'ticket_id' => $ticket->id,
                            'admin_id' => $user->id,
                            'admin_email' => $user->email,
                        ]);
                        
                        $user->notify(new TicketCreated($ticket));
                        $notifiedUsers->push($user->id);
                    } catch (\Exception $e) {
                        \Log::error('Failed to send ticket created notification to Super Admin', [
                            'ticket_id' => $ticket->id,
                            'admin_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
        } catch (\Exception $e) {
            \Log::error('Failed to notify Super Admin users for ticket creation', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Notify Admin Unit users for this ticket's unit
        if ($ticket->unit_id) {
            try {
                $accountSettings = app(AccountSettings::class);
                
                $adminUnitUsers = User::role('Admin Unit')
                    ->where('unit_id', $ticket->unit_id)
                    ->whereNotNull('email')
                    ->when($accountSettings->user_email_verification, function ($query) {
                        $query->whereNotNull('email_verified_at');
                    })
                    ->where('id', '!=', $authUser?->id)
                    ->get();
                
                $adminUnitUsers->each(function ($user) use ($ticket, &$notifiedUsers) {
                    if (!$notifiedUsers->contains($user->id)) {
                        try {
                            \Log::info('Sending ticket created notification to Admin Unit', [
                                'ticket_id' => $ticket->id,
                                'unit_id' => $ticket->unit_id,
                                'admin_id' => $user->id,
                                'admin_email' => $user->email,
                            ]);
                            
                            $user->notify(new TicketCreated($ticket));
                            $notifiedUsers->push($user->id);
                        } catch (\Exception $e) {
                            \Log::error('Failed to send ticket created notification to Admin Unit', [
                                'ticket_id' => $ticket->id,
                                'admin_id' => $user->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                });
            } catch (\Exception $e) {
                \Log::error('Failed to notify Admin Unit users for ticket creation', [
                    'ticket_id' => $ticket->id,
                    'unit_id' => $ticket->unit_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Notify Staff Unit and Global Staff (existing functionality)
        $staffUsers = new Collection([]);
        $accountSettings = app(AccountSettings::class);

        $usersQuery = User::whereNotNull('email')
            ->when($accountSettings->user_email_verification, function ($query) {
                $query->whereNotNull('email_verified_at');
            });

        $usersQuery->clone()->role('Staff Unit')
            ->where('unit_id', $ticket->unit_id)
            ->get()
            ->each(function ($user) use (&$staffUsers) {
                $staffUsers->put($user->id, $user);
            });

        $usersQuery->clone()->role('Global Staff')
            ->get()
            ->each(function ($user) use (&$staffUsers) {
                $staffUsers->put($user->id, $user);
            });

        if ($authUser && $staffUsers->has($authUser->id)) {
            $staffUsers->pull($authUser->id);
        }

        // Only notify staff users we haven't already notified
        $staffUsers->reject(function ($user) use ($notifiedUsers) {
            return $notifiedUsers->contains($user->id);
        })->each(function ($user) use ($ticket) {
            try {
                $user->notify(new TicketCreated($ticket));
            } catch (\Exception $e) {
                \Log::error('Failed to send ticket created notification to staff', [
                    'ticket_id' => $ticket->id,
                    'staff_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        if (
            array_key_exists('ticket_statuses_id', $ticket->getDirty())
            && (
                (
                    array_key_exists('ticket_statuses_id', $ticket->getOriginal())
                    && $ticket->getDirty()['ticket_statuses_id'] != $ticket->getOriginal()['ticket_statuses_id']
                )
                || ! array_key_exists('ticket_statuses_id', $ticket->getOriginal())
            )
        ) {
            $authUser = auth()->user();
            
            // Eager load relationships to avoid N+1 queries
            $ticket->loadMissing(['owner', 'responsible', 'comments.user']);
            
            $subscribers = $ticket->getSubscribers();

            if ($subscribers->has($authUser->id)) {
                $subscribers->pull($authUser->id);
            }

            $subscribers->each(fn($subscriber) => $subscriber->notify(new TicketStatusUpdated($ticket)));
        }
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        if (!$ticket->owner) {
            return; // Guest ticket, no owner to notify
        }
        
        $authUser = auth()->user();
        if ($ticket->owner->id != $authUser->id) {
            $ticket->owner->notify(new TicketDeleted($ticket));
        }
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        if (!$ticket->owner) {
            return; // Guest ticket, no owner to notify
        }
        
        $authUser = auth()->user();
        if ($ticket->owner->id != $authUser->id) {
            $ticket->owner->notify(new TicketRestored($ticket));
        }
    }

    /**
     * Handle the Ticket "force deleted" event.
     */
    public function forceDeleted(Ticket $ticket): void
    {
        //
    }
}
