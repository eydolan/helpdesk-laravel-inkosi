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
     * Display password reset form (phone number input)
     *
     * @return \Illuminate\View\View
     */
    public function showResetForm()
    {
        return view('auth.password.reset');
    }

    /**
     * Send reset code via SMS to phone number
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetCode(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $validated['phone'];

        // Find user by phone
        $user = User::byPhone($phone)->first();

        if (!$user) {
            return back()->withErrors(['phone' => 'No account found with this phone number.']);
        }

        // Generate reset code
        $code = $this->passwordService->sendPasswordReset($user);

        // Store reset code in database
        PasswordResetCode::create([
            'phone' => $phone,
            'code' => $code,
            'expires_at' => now()->addMinutes(15),
        ]);

        return redirect()->route('password.reset.verify')
            ->with('success', 'Password reset code has been sent to your phone number.')
            ->with('phone', $phone);
    }

    /**
     * Display code verification form
     *
     * @return \Illuminate\View\View
     */
    public function showVerifyForm()
    {
        $phone = session('phone');
        
        if (!$phone) {
            return redirect()->route('password.request');
        }

        return view('auth.password.verify', ['phone' => $phone]);
    }

    /**
     * Verify reset code and show password change form
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyResetCode(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $resetCode = PasswordResetCode::where('phone', $validated['phone'])
            ->where('code', $validated['code'])
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$resetCode || !$resetCode->isValid()) {
            return back()->withErrors(['code' => 'Invalid or expired reset code.']);
        }

        // Store verified code in session for password update
        session([
            'reset_code_id' => $resetCode->id,
            'reset_phone' => $validated['phone'],
        ]);

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
        $phone = session('reset_phone');

        if (!$resetCodeId || !$phone) {
            return redirect()->route('password.request')
                ->withErrors(['error' => 'Reset session expired. Please try again.']);
        }

        $resetCode = PasswordResetCode::find($resetCodeId);

        if (!$resetCode || !$resetCode->isValid() || $resetCode->phone !== $phone) {
            return redirect()->route('password.request')
                ->withErrors(['error' => 'Invalid reset code. Please try again.']);
        }

        // Find user and update password
        $user = User::byPhone($phone)->first();

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
        session()->forget(['reset_code_id', 'reset_phone']);

        return redirect()->route('filament.admin.auth.login')
            ->with('success', 'Password reset successfully. Please login with your new password.');
    }
}
