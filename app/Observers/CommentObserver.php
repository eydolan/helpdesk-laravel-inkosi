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
        
        $subscribers = $comment->ticket->getSubscribers();

        if ($subscribers->has($authUser->id)) {
            $subscribers->pull($authUser->id);
        }

        $subscribers->each(fn ($subscriber) => $subscriber->notify(new TicketCommentCreated($comment)));
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
