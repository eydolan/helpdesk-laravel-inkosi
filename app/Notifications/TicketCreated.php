<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Settings\GeneralSettings;
use App\Support\Notifications\Debounce;
use App\Support\Notifications\ShouldBeDebounce;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class TicketCreated extends Notification implements ShouldBeDebounce, ShouldQueue
{
    use Debounce;
    use Queueable;

    protected $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function viaDebounce(object $notifiable): array
    {
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

        if ($notifiable instanceof AnonymousNotifiable) {
            if (is_array($notifiable->routes[$channel])) {
                $notifiableId = implode(';', array_keys($notifiable->routes[$channel]));
            } else {
                $notifiableId = $notifiable->routes[$channel];
            }
        } else {
            $notifiableId = $notifiable->id;
        }

        return "{$className}-{$channel}-{$notifiableId}:{$this->ticket->id}";
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $siteTitle = app(GeneralSettings::class)->site_title;
        $subjectPrefix = "[{$siteTitle}] ";

        // Eager load relationships for email content
        $this->ticket->loadMissing(['owner', 'category', 'priority', 'unit']);

        $message = (new MailMessage)
            ->subject($subjectPrefix.__('Ticket #:ticket created', [
                'ticket' => $this->ticket->id,
            ]))
            ->greeting(__('New Ticket Created').": #{$this->ticket->id}")
            ->line(__('Title').": {$this->ticket->title}");

        // Add description
        if ($this->ticket->description) {
            $message->line(new HtmlString('<strong>'.__('Description').':</strong>'))
                ->line(new HtmlString(nl2br(e($this->ticket->description))));
        }

        // Add ticket details
        $details = [];
        if ($this->ticket->owner) {
            $details[] = __('Owner').": {$this->ticket->owner->name} ({$this->ticket->owner->email})";
        }
        if ($this->ticket->category) {
            $details[] = __('Category').": {$this->ticket->category->name}";
        }
        if ($this->ticket->priority) {
            $details[] = __('Priority').": {$this->ticket->priority->name}";
        }
        if ($this->ticket->unit) {
            $details[] = __('Unit').": {$this->ticket->unit->name}";
        }
        if ($this->ticket->voucher_number) {
            $details[] = __('Voucher Number').": {$this->ticket->voucher_number}";
        }

        if (!empty($details)) {
            $message->line('')
                ->line(__('Ticket Details').':')
                ->lines($details);
        }

        return $message->action(__('View Ticket'), route('filament.admin.resources.tickets.view', $this->ticket));
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('Ticket #:ticket created', [
                'ticket' => $this->ticket->id,
            ]))
            ->body($this->ticket->title)
            ->actions([
                Action::make('view')
                    ->translateLabel()
                    ->button()
                    ->url(route('filament.admin.resources.tickets.view', $this->ticket)),
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
