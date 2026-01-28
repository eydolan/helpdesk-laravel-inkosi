<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserResolutionService
{
    protected PasswordService $passwordService;

    public function __construct(PasswordService $passwordService)
    {
        $this->passwordService = $passwordService;
    }

    /**
     * Resolve or create user account
     * Returns array with user, is_new flag, and password (if new)
     *
     * @param array $data ['name', 'email'?, 'phone']
     * @param bool $sendPassword Whether to send password to user
     * @return array ['user' => User, 'is_new' => bool, 'password' => string|null]
     */
    public function resolveOrCreate(array $data, bool $sendPassword = true): array
    {
        // If user is authenticated, return current user
        if (Auth::check()) {
            return [
                'user' => Auth::user(),
                'is_new' => false,
                'password' => null,
            ];
        }

        $email = $data['email'] ?? null;
        $phone = $data['phone'] ?? null;
        $name = $data['name'] ?? null;

        if (!$phone) {
            throw new \InvalidArgumentException('Phone number is required');
        }

        // Search for existing user by email first (if provided)
        $user = null;
        if ($email) {
            $user = User::where('email', $email)->first();
        }

        // If not found by email, search by phone
        if (!$user && $phone) {
            $user = User::where('phone', $phone)->first();
        }

        // If user found, update info if needed
        if ($user) {
            $updated = false;
            
            // Update name if provided and different
            if ($name && $user->name !== $name) {
                $user->name = $name;
                $updated = true;
            }
            
            // Update email if provided and user doesn't have one
            if ($email && !$user->email) {
                $user->email = $email;
                $updated = true;
            }
            
            // Update phone if provided and different
            if ($phone && $user->phone !== $phone) {
                $user->phone = $phone;
                $updated = true;
            }
            
            if ($updated) {
                $user->save();
            }

            return [
                'user' => $user,
                'is_new' => false,
                'password' => null,
            ];
        }

        // Create new user
        $password = $this->passwordService->generatePassword();
        
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => Hash::make($password),
            'email_verified_at' => now(), // Auto-verify since they're actively using the system
            'is_active' => true,
        ]);

        // Send password if requested
        if ($sendPassword) {
            $this->passwordService->sendPassword($user, $password);
        }

        Log::info('New user created from public ticket submission', [
            'user_id' => $user->id,
            'email' => $email,
            'phone' => $phone,
        ]);

        return [
            'user' => $user,
            'is_new' => true,
            'password' => $password,
        ];
    }
}
