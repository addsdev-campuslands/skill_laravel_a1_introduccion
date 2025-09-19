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
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\ValidationException;

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
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|lowercase|email|max:255',
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
        } catch (ValidationException $e) {
            // 🔹 Redirige de vuelta con errores en sesión (Inertia los captura)
            return back()->withErrors($e->errors())->withInput();
        }

        // Detectar si viene por OAuth
        $isOAuth = $request->password === 'oauth-generated';

        // Crear o recuperar usuario
        $user = User::firstOrCreate(
            ['email' => $request->email],
            [
                'name' => $request->name,
                'password' => $request->password && $request->password !== 'oauth-generated'
                    ? Hash::make($request->password)
                    : Hash::make(str()->random(32)),
                'provider' => $request->input('provider'),
                'avatar'   => $request->input('avatar'),
            ]
        );

        // Disparar evento solo si es nuevo
        if ($user->wasRecentlyCreated) {
            event(new Registered($user));
        }

        // Loguear al usuario
        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
