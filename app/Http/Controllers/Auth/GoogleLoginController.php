<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Mail\NewUserAdminNotificationMail;
use App\Mail\WelcomeUserMail;
use Illuminate\Support\Facades\Mail;

class GoogleLoginController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google authentication.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $googleId = $googleUser->getId();
            $email = $googleUser->getEmail();
            $avatar = $googleUser->getAvatar();

            // Try to find user by Google ID first, then by email
            $user = User::where('google_id', $googleId)->orWhere('email', $email)->first();

            if ($user) {
                // Update Google ID if missing
                if (empty($user->google_id)) {
                    $user->google_id = $googleId;
                    $user->save();
                }

                   if (empty($user->profile_photo_path)) {
                    $user->profile_photo_path = $avatar;
                    $user->save();
                }

                // Force email verification
                if (is_null($user->email_verified_at)) {
                    $user->forceFill(['email_verified_at' => now()])->save();
                }
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName() ?? 'User',
                    'email' => $email,
                    'role' => 'user',
                    'password' => Hash::make(\Str::random(24)), // Random password
                    'google_id' => $googleId,
                    'profile_photo_path' => $avatar,
                    'signup_method' => 'google',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);

                // --- EMAIL LOGIC STARTS HERE ---

    // 1. Send Welcome Email to the User (QUEUED)
    Mail::to($user->email)->queue(new WelcomeUserMail($user));

    // 2. Send Admin Notification Email (QUEUED)
    // The admin's email is specified directly as requested
    Mail::to('ronaldjjuuko7@gmail.com')->queue(new NewUserAdminNotificationMail($user));

    // --- EMAIL LOGIC ENDS HERE ---

              
            }

            Auth::login($user);

            $name = $user->name;
            return redirect()->intended(route('dashboard', absolute: false))
                ->with('show_welcome', true)
                ->with('success', "Login Successful. Welcome back $name!");

        } catch (\Exception $e) {
            \Log::error('Google login error: ' . $e->getMessage());
            return redirect(route('login'))->withErrors([
                'google_error' => 'Unable to authenticate with Google. Please try again.'
            ]);
        }
    }
}
