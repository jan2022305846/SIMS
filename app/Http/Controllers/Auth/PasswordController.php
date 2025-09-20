<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    /**
     * Show the form for setting a new password.
     */
    public function showSetForm($token)
    {
        return view('auth.passwords.set', ['token' => $token]);
    }

    /**
     * Reset the user's password.
     */
    public function set(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'must_set_password' => false,
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            // Auto-login the user after password reset
            $user = User::where('email', $request->email)->first();
            if ($user) {
                Auth::login($user);
                return redirect()->route('dashboard')->with('success', 'Password set successfully! Welcome to SIMS.');
            }
        }

        return back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * Show the forgot password form.
     */
    public function showForgotForm()
    {
        return view('auth.passwords.forgot');
    }

    /**
     * Send password reset link.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['school_id' => 'required|string']);

        $user = User::where('school_id', $request->school_id)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this school ID.'
            ], 404);
        }

        // Check if user has an email address
        if (!$user->email) {
            return response()->json([
                'success' => false,
                'message' => 'No email address is associated with this account. Please contact your administrator.'
            ], 400);
        }

        try {
            // Generate password reset token and send email
            $token = Password::createToken($user);
            $user->notify(new \App\Notifications\SetPasswordNotification($token, false));

            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent! Please check your email.'
            ]);

        } catch (\Exception $e) {
            Log::error('Password reset failed for user ' . $user->school_id . ': ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send reset email. Please try again later or contact your administrator.'
            ], 500);
        }
    }
}
