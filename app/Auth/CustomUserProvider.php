<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class CustomUserProvider extends EloquentUserProvider implements UserProvider
{
    /**
     * Retrieve a user by the given credentials.
     * Supports both email and phone number lookup.
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials)) {
            return null;
        }

        // If phone is provided in credentials, use phone lookup
        if (isset($credentials['phone'])) {
            $user = User::where('phone', $credentials['phone'])->first();
            if ($user) {
                return $user;
            }
        }

        // Check if 'email' field contains a phone number (not a valid email)
        if (isset($credentials['email'])) {
            $email = trim($credentials['email']);
            
            // If it's a valid email, use case-insensitive email lookup
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
            } else {
                // It's a phone number stored in email field
                return User::where('phone', $email)->first();
            }
        }

        return null;
    }

    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $plain = $credentials['password'] ?? null;

        if (!$plain) {
            return false;
        }

        // Check if user is active (default to true if null for backward compatibility)
        if ($user->is_active === false) {
            return false;
        }

        return $this->hasher->check($plain, $user->getAuthPassword());
    }
}
