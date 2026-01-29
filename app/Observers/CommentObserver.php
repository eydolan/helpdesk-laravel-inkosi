<?php

namespace App\Observers;

use App\Models\Comment;
use App\Notifications\TicketCommentCreated;

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
        
        // ALWAYS notify the ticket owner (unless they're the one who commented)
        $notifyOwner = $ticket->owner && $ticket->owner->id !== $authUser->id;
        
        if ($notifyOwner) {
            try {
                \Log::info('Sending comment notification to ticket owner', [
                    'ticket_id' => $ticket->id,
                    'owner_id' => $ticket->owner->id,
                    'owner_email' => $ticket->owner->email,
                    'owner_phone' => $ticket->owner->phone,
                    'comment_id' => $comment->id,
                ]);
                
                $ticket->owner->notify(new TicketCommentCreated($comment));
            } catch (\Exception $e) {
                \Log::error('Failed to send comment notification to ticket owner', [
                    'ticket_id' => $ticket->id,
                    'owner_id' => $ticket->owner->id,
                    'comment_id' => $comment->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
        
        // Also notify other subscribers (responsible, commenters)
        $subscribers = $ticket->getSubscribers();

        // Remove the comment author and owner from subscribers (already handled above)
        if ($subscribers->has($authUser->id)) {
            $subscribers->pull($authUser->id);
        }
        if ($ticket->owner && $subscribers->has($ticket->owner->id)) {
            $subscribers->pull($ticket->owner->id);
        }

        // Send notifications to other subscribers
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
