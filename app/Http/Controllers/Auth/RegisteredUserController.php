<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Mail\NewUserAdminNotificationMail;
use App\Mail\WelcomeUserMail;
use Illuminate\Support\Facades\Mail;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'signup_method' => 'email',
            'status' => 'active',
            'role' => 'user',
        ]);

        // --- EMAIL LOGIC STARTS HERE ---

        // 1. Send Welcome Email to the User (QUEUED)
        Mail::to($user->email)->queue(new WelcomeUserMail($user));

        // 2. Send Admin Notification Email (QUEUED)
        // The admin's email is specified directly as requested
        Mail::to('ronaldjjuuko7@gmail.com')->queue(new NewUserAdminNotificationMail($user));

        // --- EMAIL LOGIC ENDS HERE ---

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
