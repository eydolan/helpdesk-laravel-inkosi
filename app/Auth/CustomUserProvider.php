<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class CustomUserProvider extends EloquentUserProvider implements UserProvider
{
    /**
     * Normalize phone number by removing all non-numeric characters
     * This ensures consistent matching regardless of formatting
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove all non-numeric characters
        return preg_replace('/[^0-9]/', '', $phone);
    }

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
            $phone = trim($credentials['phone']);
            $normalizedPhone = $this->normalizePhone($phone);
            
            // Try exact match first (fastest)
            $user = User::where('phone', $phone)->first();
            if ($user) {
                return $user;
            }
            
            // Try normalized match using database query
            // Use REGEXP_REPLACE if available (MySQL 8.0+), otherwise fallback to PHP normalization
            try {
                $user = User::whereRaw("REGEXP_REPLACE(phone, '[^0-9]', '') = ?", [$normalizedPhone])
                    ->whereNotNull('phone')
                    ->first();
                if ($user) {
                    return $user;
                }
            } catch (\Exception $e) {
                // Fallback to PHP-based normalization for older MySQL versions
            }
            
            // Fallback: Get users with phone and compare normalized versions
            $users = User::whereNotNull('phone')
                ->where('phone', 'LIKE', '%' . substr($normalizedPhone, -8) . '%') // Optimize: match last 8 digits
                ->get();
            foreach ($users as $u) {
                if ($this->normalizePhone($u->phone) === $normalizedPhone) {
                    return $u;
                }
            }
        }

        // Check if 'email' field contains a phone number (not a valid email)
        if (isset($credentials['email'])) {
            $email = trim($credentials['email']);
            
            // If it's a valid email, use case-insensitive email lookup
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
            } else {
                // It's a phone number stored in email field - normalize and compare
                $normalizedPhone = $this->normalizePhone($email);
                
                // Try exact match first
                $user = User::where('phone', $email)->first();
                if ($user) {
                    return $user;
                }
                
                // Try normalized match using database query
                try {
                    $user = User::whereRaw("REGEXP_REPLACE(phone, '[^0-9]', '') = ?", [$normalizedPhone])
                        ->whereNotNull('phone')
                        ->first();
                    if ($user) {
                        return $user;
                    }
                } catch (\Exception $e) {
                    // Fallback to PHP-based normalization
                }
                
                // Fallback: Get users with phone and compare normalized versions
                $users = User::whereNotNull('phone')
                    ->where('phone', 'LIKE', '%' . substr($normalizedPhone, -8) . '%')
                    ->get();
                foreach ($users as $u) {
                    if ($this->normalizePhone($u->phone) === $normalizedPhone) {
                        return $u;
                    }
                }
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
