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
            $user = User::byPhone($credentials['phone'])->first();
            if ($user) {
                return $user;
            }
        }

        // Check if 'email' field contains a phone number (not a valid email)
        if (isset($credentials['email'])) {
            $email = $credentials['email'];
            
            // If it's a valid email, use standard email lookup
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return User::byEmail($email)->first();
            } else {
                // It's a phone number stored in email field
                return User::byPhone($email)->first();
            }
        }

        // Fallback to standard email lookup
        if (isset($credentials['email'])) {
            return User::byEmail($credentials['email'])->first();
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

        // Check if user is active
        if (method_exists($user, 'is_active') && !$user->is_active) {
            return false;
        }

        return $this->hasher->check($plain, $user->getAuthPassword());
    }
}
