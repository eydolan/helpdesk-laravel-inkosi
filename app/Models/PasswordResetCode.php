<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetCode extends Model
{
    protected $fillable = [
        'email',
        'phone',
        'code',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Check if code is valid (not expired and not used)
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return !$this->used_at && $this->expires_at->isFuture();
    }

    /**
     * Mark code as used
     *
     * @return void
     */
    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }
}
