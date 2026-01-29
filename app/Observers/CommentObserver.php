<?php

namespace App\Observers;

use App\Models\Comment;
use App\Models\User;
use App\Notifications\TicketCommentCreated;
use Illuminate\Notifications\AnonymousNotifiable;

class CommentObserver
{
    /**
     * Handle the Comment "created" event.
     */
    public function created(Comment $comment): void
    {
        $authUser = auth()->user();
        
        // Eager load ticket with necessary relationships to avoid N+1 queries
        $comment->loadMissing(['ticket.owner', 'ticket.responsible', 'ticket.comments.user']);
        
        $ticket = $comment->ticket;
        
        $notifiedUsers = collect([$authUser->id]); // Track users we've notified to avoid duplicates
        
        // ALWAYS notify the ticket owner (unless they're the one who commented)
        if ($ticket->owner && $ticket->owner->id !== $authUser->id) {
            try {
                \Log::info('Sending comment notification to ticket owner', [
                    'ticket_id' => $ticket->id,
                    'owner_id' => $ticket->owner->id,
                    'owner_email' => $ticket->owner->email,
                    'owner_phone' => $ticket->owner->phone,
                    'comment_id' => $comment->id,
                ]);
                
                $ticket->owner->notify(new TicketCommentCreated($comment));
                $notifiedUsers->push($ticket->owner->id);
            } catch (\Exception $e) {
                \Log::error('Failed to send comment notification to ticket owner', [
                    'ticket_id' => $ticket->id,
                    'owner_id' => $ticket->owner->id,
                    'comment_id' => $comment->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        } elseif (!$ticket->owner && ($ticket->guest_email || $ticket->guest_phone)) {
            // Handle guest tickets - send notification to guest email/phone
            try {
                $guestNotifiable = new AnonymousNotifiable();
                
                // Determine notification address (email or SMS)
                // Check if guest email is an SMS gateway address
                $hasGuestEmail = $ticket->guest_email 
                    && !str_ends_with($ticket->guest_email, '@winsms.net');
                
                if ($hasGuestEmail) {
                    $guestNotifiable->route('mail', $ticket->guest_email);
                    \Log::info('Sending comment notification to guest email', [
                        'ticket_id' => $ticket->id,
                        'guest_email' => $ticket->guest_email,
                        'comment_id' => $comment->id,
                    ]);
                } elseif ($ticket->guest_phone) {
                    // Send SMS via email-to-SMS gateway
                    $smsEmail = ($ticket->guest_email && str_ends_with($ticket->guest_email, '@winsms.net'))
                        ? $ticket->guest_email
                        : ($ticket->guest_phone . '@winsms.net');
                    $guestNotifiable->route('mail', $smsEmail);
                    \Log::info('Sending comment notification to guest phone via SMS', [
                        'ticket_id' => $ticket->id,
                        'guest_phone' => $ticket->guest_phone,
                        'sms_email' => $smsEmail,
                        'comment_id' => $comment->id,
                    ]);
                }
                
                if ($hasGuestEmail || $ticket->guest_phone) {
                    $guestNotifiable->notify(new TicketCommentCreated($comment));
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send comment notification to guest', [
                    'ticket_id' => $ticket->id,
                    'guest_email' => $ticket->guest_email,
                    'guest_phone' => $ticket->guest_phone,
                    'comment_id' => $comment->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
        
        // ALWAYS notify the responsible user (unless they're the commenter or already notified)
        if ($ticket->responsible && 
            $ticket->responsible->id !== $authUser->id && 
            !$notifiedUsers->contains($ticket->responsible->id)) {
            try {
                \Log::info('Sending comment notification to responsible user', [
                    'ticket_id' => $ticket->id,
                    'responsible_id' => $ticket->responsible->id,
                    'responsible_email' => $ticket->responsible->email,
                    'responsible_phone' => $ticket->responsible->phone,
                    'comment_id' => $comment->id,
                ]);
                
                $ticket->responsible->notify(new TicketCommentCreated($comment));
                $notifiedUsers->push($ticket->responsible->id);
            } catch (\Exception $e) {
                \Log::error('Failed to send comment notification to responsible user', [
                    'ticket_id' => $ticket->id,
                    'responsible_id' => $ticket->responsible->id,
                    'comment_id' => $comment->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        // Notify Super Admin users
        try {
            $superAdmins = User::role('Super Admin')
                ->whereNotNull('email')
                ->where('id', '!=', $authUser->id)
                ->get();
            
            $superAdmins->each(function ($user) use ($comment, $ticket, &$notifiedUsers) {
                if (!$notifiedUsers->contains($user->id)) {
                    try {
                        \Log::info('Sending comment notification to Super Admin', [
                            'ticket_id' => $ticket->id,
                            'admin_id' => $user->id,
                            'admin_email' => $user->email,
                            'comment_id' => $comment->id,
                        ]);
                        
                        $user->notify(new TicketCommentCreated($comment));
                        $notifiedUsers->push($user->id);
                    } catch (\Exception $e) {
                        \Log::error('Failed to send comment notification to Super Admin', [
                            'ticket_id' => $ticket->id,
                            'admin_id' => $user->id,
                            'comment_id' => $comment->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
        } catch (\Exception $e) {
            \Log::error('Failed to notify Super Admin users for comment', [
                'ticket_id' => $ticket->id,
                'comment_id' => $comment->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Notify Admin Unit users for this ticket's unit
        if ($ticket->unit_id) {
            try {
                $adminUnitUsers = User::role('Admin Unit')
                    ->where('unit_id', $ticket->unit_id)
                    ->whereNotNull('email')
                    ->where('id', '!=', $authUser->id)
                    ->get();
                
                $adminUnitUsers->each(function ($user) use ($comment, $ticket, &$notifiedUsers) {
                    if (!$notifiedUsers->contains($user->id)) {
                        try {
                            \Log::info('Sending comment notification to Admin Unit', [
                                'ticket_id' => $ticket->id,
                                'unit_id' => $ticket->unit_id,
                                'admin_id' => $user->id,
                                'admin_email' => $user->email,
                                'comment_id' => $comment->id,
                            ]);
                            
                            $user->notify(new TicketCommentCreated($comment));
                            $notifiedUsers->push($user->id);
                        } catch (\Exception $e) {
                            \Log::error('Failed to send comment notification to Admin Unit', [
                                'ticket_id' => $ticket->id,
                                'admin_id' => $user->id,
                                'comment_id' => $comment->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                });
            } catch (\Exception $e) {
                \Log::error('Failed to notify Admin Unit users for comment', [
                    'ticket_id' => $ticket->id,
                    'unit_id' => $ticket->unit_id,
                    'comment_id' => $comment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        // Also notify other subscribers (commenters) - but exclude already notified users
        $subscribers = $ticket->getSubscribers();

        // Remove users we've already notified
        $subscribers = $subscribers->reject(function ($subscriber) use ($notifiedUsers) {
            return $notifiedUsers->contains($subscriber->id);
        });

        // Send notifications to remaining subscribers
        $subscribers->each(function ($subscriber) use ($comment) {
            try {
                \Log::info('Sending comment notification to subscriber', [
                    'subscriber_id' => $subscriber->id,
                    'comment_id' => $comment->id,
                ]);
                
                $subscriber->notify(new TicketCommentCreated($comment));
            } catch (\Exception $e) {
                \Log::error('Failed to send comment notification to subscriber', [
                    'subscriber_id' => $subscriber->id,
                    'comment_id' => $comment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    /**
     * Handle the Comment "deleted" event.
     */
    public function deleted(Comment $comment): void
    {
        //
    }

    /**
     * Handle the Comment "restored" event.
     */
    public function restored(Comment $comment): void
    {
        //
    }

    /**
     * Handle the Comment "force deleted" event.
     */
    public function forceDeleted(Comment $comment): void
    {
        //
    }
}
