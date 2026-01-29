<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetCode;
use App\Models\User;
use App\Services\PasswordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    protected PasswordService $passwordService;

    public function __construct(PasswordService $passwordService)
    {
        $this->passwordService = $passwordService;
    }

    /**
     * Display password reset form (email or phone number input)
     *
     * @return \Illuminate\View\View
     */
    public function showResetForm()
    {
        return view('auth.password.reset');
    }

    /**
     * Send reset code via email or SMS
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetCode(Request $request)
    {
        $validated = $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ], [
            'email.email' => 'Please enter a valid email address.',
        ]);

        $user = null;
        $identifier = null;
        $identifierType = null;

        // Try to find user by email first
        if (!empty($validated['email'])) {
            $user = User::whereRaw('LOWER(email) = ?', [strtolower($validated['email'])])->first();
            if ($user) {
                $identifier = $user->email;
                $identifierType = 'email';
            }
        }

        // If not found by email, try phone
        if (!$user && !empty($validated['phone'])) {
            $user = User::byPhone($validated['phone'])->first();
            if ($user) {
                $identifier = $user->phone;
                $identifierType = 'phone';
                
                // Ensure user has email set to phone@winsms.net for email-to-SMS functionality
                // Only set if email is missing or already a winsms email (update if phone changed)
                if (!$user->email || str_ends_with($user->email, '@winsms.net')) {
                    $winsmsEmail = $user->phone . '@winsms.net';
                    if ($user->email !== $winsmsEmail) {
                        $user->email = $winsmsEmail;
                        $user->save();
                    }
                }
            }
        }

        // If still not found, check if phone was provided but user has email
        if (!$user && !empty($validated['phone'])) {
            // Maybe they entered email in phone field
            if (filter_var($validated['phone'], FILTER_VALIDATE_EMAIL)) {
                $user = User::whereRaw('LOWER(email) = ?', [strtolower($validated['phone'])])->first();
                if ($user) {
                    $identifier = $user->email;
                    $identifierType = 'email';
                }
            }
        }

        if (!$user) {
            $errorField = !empty($validated['email']) ? 'email' : 'phone';
            $errorMessage = !empty($validated['email']) 
                ? 'No account found with this email address.' 
                : 'No account found with this phone number.';
            return back()->withErrors([$errorField => $errorMessage])->withInput();
        }

        // Generate reset code - use the method based on identifier type
        $code = $this->passwordService->sendPasswordReset($user, $identifierType);

        // Store reset code in database
        $resetData = [
            'code' => $code,
            'expires_at' => now()->addMinutes(15),
        ];

        if ($identifierType === 'email') {
            $resetData['email'] = $identifier;
        } else {
            $resetData['phone'] = $identifier;
        }

        PasswordResetCode::create($resetData);

        $successMessage = $identifierType === 'email'
            ? 'Password reset code has been sent to your email address.'
            : 'Password reset code has been sent to your phone number.';

        $sessionData = [
            'success' => $successMessage,
            $identifierType => $identifier,
        ];

        return redirect()->route('password.reset.verify')
            ->with($sessionData);
    }

    /**
     * Display code verification form
     *
     * @return \Illuminate\View\View
     */
    public function showVerifyForm(Request $request)
    {
        $email = session('email') ?? $request->query('email');
        $phone = session('phone') ?? $request->query('phone');
        
        // If no identifier in session or query, show form but allow manual entry
        // User can enter their email/phone and code
        
        return view('auth.password.verify', [
            'email' => $email,
            'phone' => $phone,
        ]);
    }

    /**
     * Verify reset code and show password change form
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyResetCode(Request $request)
    {
        // Get email/phone from request or session
        $email = $request->input('email') ?: session('email');
        $phone = $request->input('phone') ?: session('phone');
        
        $validated = $request->validate([
            'email' => $email ? 'nullable|email' : 'required_without:phone|email',
            'phone' => $phone ? 'nullable|string' : 'required_without:email|string',
            'code' => 'required|string|size:6',
        ]);

        // Use validated data or fallback to session/request
        $validated['email'] = $validated['email'] ?? $email;
        $validated['phone'] = $validated['phone'] ?? $phone;

        if (empty($validated['email']) && empty($validated['phone'])) {
            return back()->withErrors(['email' => 'Email or phone number is required.'])->withInput();
        }

        $query = PasswordResetCode::where('code', $validated['code'])
            ->whereNull('used_at')
            ->where('expires_at', '>', now());

        if (!empty($validated['email'])) {
            $query->where('email', $validated['email']);
        } else {
            $query->where('phone', $validated['phone']);
        }

        $resetCode = $query->first();

        if (!$resetCode || !$resetCode->isValid()) {
            return back()->withErrors(['code' => 'Invalid or expired reset code.']);
        }

        // Store verified code in session for password update
        $sessionData = [
            'reset_code_id' => $resetCode->id,
        ];

        if (!empty($validated['email'])) {
            $sessionData['reset_email'] = $validated['email'];
        } else {
            $sessionData['reset_phone'] = $validated['phone'];
        }

        session($sessionData);

        return redirect()->route('password.update.form')
            ->with('success', 'Code verified. Please enter your new password.');
    }

    /**
     * Display password update form
     *
     * @return \Illuminate\View\View
     */
    public function showUpdateForm()
    {
        if (!session('reset_code_id')) {
            return redirect()->route('password.request');
        }

        return view('auth.password.update');
    }

    /**
     * Update password after code verification
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $resetCodeId = session('reset_code_id');
        $email = session('reset_email');
        $phone = session('reset_phone');

        if (!$resetCodeId || (!$email && !$phone)) {
            return redirect()->route('password.request')
                ->withErrors(['error' => 'Reset session expired. Please try again.']);
        }

        $resetCode = PasswordResetCode::find($resetCodeId);

        if (!$resetCode || !$resetCode->isValid()) {
            return redirect()->route('password.request')
                ->withErrors(['error' => 'Invalid reset code. Please try again.']);
        }

        // Verify identifier matches
        if ($email && $resetCode->email !== $email) {
            return redirect()->route('password.request')
                ->withErrors(['error' => 'Invalid reset code. Please try again.']);
        }
        if ($phone && $resetCode->phone !== $phone) {
            return redirect()->route('password.request')
                ->withErrors(['error' => 'Invalid reset code. Please try again.']);
        }

        // Find user and update password
        if ($email) {
            $user = User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
        } else {
            $user = User::byPhone($phone)->first();
        }

        if (!$user) {
            return redirect()->route('password.request')
                ->withErrors(['error' => 'User not found.']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Mark reset code as used
        $resetCode->markAsUsed();

        // Clear session
        session()->forget(['reset_code_id', 'reset_email', 'reset_phone']);

        return redirect()->route('filament.admin.auth.login')
            ->with('success', 'Password reset successfully. Please login with your new password.');
    }
}
