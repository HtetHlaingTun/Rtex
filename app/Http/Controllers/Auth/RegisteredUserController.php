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
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 1. Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),

        ]);

        event(new Registered($user));

        // 2. Log them in
        Auth::login($user);

        // 3. Sync Session Logic with LoginController
        $request->session()->regenerate();
        session(['last_activity' => time()]);

        // 4. Mirror the Redirect Logic from your Login store method
        if ($user->is_viewer) {
            config(['session.lifetime' => 120]);
            session(['is_admin' => false]);
            return redirect()->route('user.dashboard');
        }

        if ($user->is_admin) {
            config(['session.lifetime' => 60]);
            session(['is_admin' => true]);
            return redirect()->route('currencies.index');
        }

        // Default for standard users
        config(['session.lifetime' => 120]);
        session(['is_admin' => false]);
        return redirect()->route('welcome');
    }
}
