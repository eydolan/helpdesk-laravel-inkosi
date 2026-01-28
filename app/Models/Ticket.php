<?php

namespace App\Models;

use App\Settings\AccountSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Ticket extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $casts = [
        'status_updated_at' => 'datetime',
    ];

    protected $fillable = [
        'priority_id',
        'unit_id',
        'owner_id',
        'category_id',
        'title',
        'description',
        'ticket_statuses_id',
        'status_updated_at',
        'responsible_id',
        'voucher_number',
        'guest_name',
        'guest_email',
        'guest_phone',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saving(function (Ticket $ticket) {
            if (array_key_exists('ticket_statuses_id', $ticket->getDirty())
                && (
                    (
                        array_key_exists('ticket_statuses_id', $ticket->getOriginal())
                        && $ticket->getDirty()['ticket_statuses_id'] != $ticket->getOriginal()['ticket_statuses_id']
                    )
                    || ! array_key_exists('ticket_statuses_id', $ticket->getOriginal())
                )
            ) {
                $ticket->status_updated_at = now();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                '*',
                'priority.name',
                'unit.name',
                'owner.name',
                'responsible.name',
                'category.name',
                'ticketStatus.name',
                'comments',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getSubscribers(): Collection
    {
        $subscribers = new Collection([]);

        if ($this->owner) {
            $subscribers[$this->owner->id] = $this->owner;
        }

        if ($this->responsible) {
            $subscribers[$this->responsible->id] = $this->responsible;
        }

        $this->comments->each(function ($comment) use (&$subscribers) {
            $subscribers->put($comment->user->id, $comment->user);
        });

        $accountSettings = app(AccountSettings::class);
        if ($accountSettings->user_email_verification) {
            $subscribers = $subscribers->filter(function ($subscriber) {
                return $subscriber->email_verified_at;
            });
        }

        return $subscribers;
    }

    /**
     * Get the priority that owns the Ticket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    /**
     * Get the unit that owns the Ticket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the owner that owns the Ticket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the responsible that owns the Ticket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    /**
     * Get the Category that owns the Ticket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the ticketStatus that owns the Ticket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ticketStatus()
    {
        return $this->belongsTo(TicketStatus::class, 'ticket_statuses_id');
    }

    /**
     * Get all of the comments for the Ticket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'tiket_id');
    }

    /**
     * Get the display name for the ticket owner (from owner or guest fields)
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->owner) {
            return $this->owner->name;
        }

        return $this->guest_name ?? 'Guest';
    }

    /**
     * Get the display email for the ticket owner (from owner or guest fields)
     *
     * @return string|null
     */
    public function getDisplayEmailAttribute(): ?string
    {
        if ($this->owner && $this->owner->email) {
            return $this->owner->email;
        }

        return $this->guest_email;
    }

    /**
     * Get the display phone for the ticket owner (from owner or guest fields)
     *
     * @return string|null
     */
    public function getDisplayPhoneAttribute(): ?string
    {
        if ($this->owner && $this->owner->phone) {
            return $this->owner->phone;
        }

        return $this->guest_phone;
    }
}
