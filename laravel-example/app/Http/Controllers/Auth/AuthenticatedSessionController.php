<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Caso OAuth
        if ($request->password === 'oauth-generated' && $request->provider) {
            $user = User::where('email', $request->email)->first();

            if ($user) {
                Auth::login($user);
                return redirect()->route('dashboard');
            }

            // Si no existe, registrarlo automáticamente
            $user = User::create([
                'name'     => $request->name ?? 'OAuth User',
                'email'    => $request->email,
                'password' => Hash::make(str()->random(32)), // contraseña aleatoria
                'provider' => $request->provider,
                'avatar'   => $request->avatar,
            ]);

            Auth::login($user);
            return redirect()->route('dashboard');
        }

        // Caso login normal
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
