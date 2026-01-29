<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

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
            
            // Update email if provided and user doesn't have one, or if user has winsms email and now has real email
            if ($email && (!$user->email || str_ends_with($user->email, '@winsms.net'))) {
                $user->email = $email;
                $updated = true;
            } elseif (!$user->email && !$email) {
                // If user has no email and none provided, set to phone@winsms.net
                $user->email = $phone . '@winsms.net';
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
        
        // If no email provided, use phone@winsms.net format for SMS delivery
        // Emails sent to phonenumber@winsms.net will be converted to SMS and sent to that phone number
        $userEmail = $email;
        if (!$userEmail) {
            $userEmail = $phone . '@winsms.net';
        }
        
        $user = User::create([
            'name' => $name,
            'email' => $userEmail,
            'phone' => $phone,
            'password' => Hash::make($password),
            'email_verified_at' => now(), // Auto-verify since they're actively using the system
            'is_active' => true,
        ]);

        // Assign default "Customer" role to new users
        $customerRole = Role::firstOrCreate([
            'name' => 'Customer',
            'guard_name' => 'web',
        ]);
        $user->assignRole($customerRole);

        // Send password if requested
        if ($sendPassword) {
            $this->passwordService->sendPassword($user, $password);
        }

        Log::info('New user created from public ticket submission', [
            'user_id' => $user->id,
            'email' => $userEmail,
            'phone' => $phone,
            'original_email_provided' => $email,
        ]);

        return [
            'user' => $user,
            'is_new' => true,
            'password' => $password,
        ];
    }
}
