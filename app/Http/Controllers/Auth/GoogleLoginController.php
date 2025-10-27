<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\ValidationException;




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

            // Find or create the user
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // User exists, log them in
                Auth::login($user);
            } else {
                // User does not exist, create new user and business
                $business = null;
                DB::transaction(function () use ($googleUser, &$user, &$business) {
                    // Create business automatically

                    $businessName = $googleUser->getName() ? $googleUser->getName() . "'s Business" : 'New Business';


                    $user = User::create([
                        'name' => $googleUser->getName() ?? 'Admin',
                        'email' => $googleUser->getEmail(),
                        'profile_photo_path' => $googleUser->getAvatar(),
                        'password' => \Hash::make(\Str::random(24)), // Create a random password since we're using Google auth
                        'status' => 'active',
                        'signup_method' => 'google',

                    ]);

                    // force email verification
                    if (is_null($user->email_verified_at)) {
                        $user->forceFill(['email_verified_at' => now()])->save();
                    }



                });




                Auth::login($user);


            }

            $name = auth()->user()->name;

            return redirect()->intended(route('dashboard', absolute: false))
                ->with('show_welcome', true)
                ->with('success', "Login Successful. Welcome back $name!");

        } catch (\Exception $e) {
            // Handle any errors that occur during the authentication process
            return redirect(route('login'))->withErrors(['google_error' => 'Unable to authenticate with Google. Please try again.']);
        }
    }






















}
