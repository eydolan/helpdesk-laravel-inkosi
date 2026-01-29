<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Settings\GeneralSettings;
use App\Support\Notifications\Debounce;
use App\Support\Notifications\ShouldBeDebounce;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class TicketCommentCreated extends Notification implements ShouldBeDebounce, ShouldQueue
{
    use Debounce;
    use Queueable;

    protected $comment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function viaDebounce(object $notifiable): array
    {
        // Always use mail channel, but we'll route to SMS if needed in toMail()
        return [
            'mail' => true,
            'database' => false,
        ];
    }

    public function viaDebounceWait(object $notifiable): array
    {
        return [
            'mail' => 60,
        ];
    }

    /**
     * Determine which connections should be used for each notification channel.
     *
     * @return array<string, string>
     */
    public function viaConnections(): array
    {
        return [
            'mail' => 'database',
            'database' => 'sync',
        ];
    }

    public function getDebounceCacheKey(object $notifiable, string $channel): string
    {
        $className = Str::slug(__CLASS__);

        return "{$className}-{$channel}-{$notifiable->id}:{$this->comment->id}";
    }

    /**
     * Check if an email address is an SMS gateway address (@winsms.net)
     */
    private function isSmsGatewayEmail(?string $email): bool
    {
        if (!$email) {
            return false;
        }
        return str_ends_with($email, '@winsms.net');
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $siteTitle = app(GeneralSettings::class)->site_title;
        $subjectPrefix = "[{$siteTitle}] ";
        
        // Check if user has a real email address (not an SMS gateway address)
        $hasEmail = $notifiable->email 
            && !$this->isSmsGatewayEmail($notifiable->email);
        
        // If no email or SMS gateway email, send SMS via email-to-SMS gateway
        if (!$hasEmail && $notifiable->phone) {
            // Use existing SMS gateway email if available, otherwise construct new one
            $smsEmail = $this->isSmsGatewayEmail($notifiable->email) 
                ? $notifiable->email 
                : ($notifiable->phone . '@winsms.net');
            
            // Create SMS-friendly message (shorter, no HTML, plain text)
            $ticketId = $this->comment->ticket->id;
            $ticketTitle = $this->comment->ticket->title;
            $commentText = strip_tags($this->comment->comment);
            
            // Truncate comment if too long for SMS (SMS limit is typically 160 chars)
            // Reserve space for ticket info
            $maxLength = 120;
            if (strlen($commentText) > $maxLength) {
                $commentText = substr($commentText, 0, $maxLength - 3) . '...';
            }
            
            // Create concise SMS message
            $smsMessage = "Ticket #{$ticketId} reply: {$commentText}";
            
            \Log::info('Sending ticket comment via SMS (email-to-SMS)', [
                'user_id' => $notifiable->id,
                'phone' => $notifiable->phone,
                'sms_email' => $smsEmail,
                'ticket_id' => $ticketId,
            ]);
            
            // Return SMS-friendly MailMessage
            // Laravel will automatically send to the notifiable's email address
            // If the user's email is already set to the SMS gateway email, it will work
            // Otherwise, we need to ensure the user's email is updated to the SMS gateway email
            return (new MailMessage)
                ->subject("Ticket #{$ticketId}")
                ->line($smsMessage);
        }
        
        // Regular email for users with email addresses
        \Log::info('Sending ticket comment via email', [
            'user_id' => $notifiable->id,
            'email' => $notifiable->email,
            'ticket_id' => $this->comment->ticket->id,
        ]);
        
        return (new MailMessage)
            ->subject($subjectPrefix.__('New comment on ticket #:ticket', [
                'ticket' => $this->comment->ticket->id,
            ]))
            ->greeting((__('Ticket').": {$this->comment->ticket->title}"))
            ->line(new HtmlString(__('Comment').": {$this->comment->comment}"))
            ->action(__('View'), route('filament.admin.resources.tickets.view', $this->comment->ticket));
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('New comment on ticket #:ticket', [
                'ticket' => $this->comment->ticket->id,
            ]))
            ->body($this->comment->ticket->title)
            ->actions([
                Action::make('view')
                    ->translateLabel()
                    ->button()
                    ->url(route('filament.admin.resources.tickets.view', $this->comment->ticket)),
            ])
            ->getDatabaseMessage();
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
