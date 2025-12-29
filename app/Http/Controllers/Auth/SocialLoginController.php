<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use App\Mail\NewUserAdminNotificationMail;
use App\Mail\WelcomeUserMail;
use Illuminate\Support\Str;

class SocialLoginController extends Controller
{
    public function redirect(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();

            $providerId = $socialUser->getId();
            $email = $socialUser->getEmail();
            $name = $socialUser->getName() ?? 'User';
            $avatar = $socialUser->getAvatar();

            $providerColumn = "{$provider}_id";

            // 1. Find by provider ID first
            $user = User::where($providerColumn, $providerId)->first();

            // 2. If not found, try email
            if (!$user && $email) {
                $user = User::where('email', $email)->first();
            }

            if ($user) {
                // Attach provider ID if missing
                if (empty($user->$providerColumn)) {
                    $user->$providerColumn = $providerId;
                }

                if (empty($user->profile_photo_path)) {
                    $user->profile_photo_path = $avatar;
                }

                if (is_null($user->email_verified_at) && $email) {
                    $user->email_verified_at = now();
                }

                $user->save();
            } else {
                // Redirect back if email is missing
                if (!$email) {
                    return redirect(route('login'))->withErrors([
                        "{$provider}_error" => "Unable to authenticate with {$provider}. Email is required."
                    ]);
                }

                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'role' => 'user',
                    'password' => Hash::make(Str::random(32)),
                    $providerColumn => $providerId,
                    'profile_photo_path' => $avatar,
                    'signup_method' => $provider,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);

                Mail::to($user->email)->queue(new WelcomeUserMail($user));
                Mail::to('ronaldjjuuko7@gmail.com')->queue(new NewUserAdminNotificationMail($user));
            }

            Auth::login($user);

            return redirect()
                ->intended(route('dashboard', absolute: false))
                ->with('show_welcome', true)
                ->with('success', "Login successful. Welcome back {$user->name}.");
        } catch (\Throwable $e) {
            Log::error(ucfirst($provider) . ' login error: ' . $e->getMessage());

            return redirect(route('login'))->withErrors([
                "{$provider}_error" => "Unable to authenticate with {$provider}. Try again."
            ]);
        }
    }
}
