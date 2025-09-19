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

        // Si el usuario ya existe, loguearlo directamente
        if (User::where('email', $request->email)->exists()) {
            $user = User::where('email', $request->email)->first();
            Auth::login($user);
            return redirect()->route('dashboard');
        }

        // Determinar qué contraseña guardar
        $passwordToStore = null;

        if ($isOAuth && $request->password === 'oauth-generated') {
            // Caso: vino de OAuth y no escribió contraseña → no tiene login tradicional
            $passwordToStore = Hash::make(str()->random(32));
        } else {
            // Caso: vino de OAuth y sí escribió contraseña, o registro normal
            $passwordToStore = Hash::make($request->password);
        }        

        // Crear nuevo usuario
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $passwordToStore,
            'provider' => $isOAuth ? $request->input('provider', 'supabase') : null,
            'avatar'   => $isOAuth ? $request->input('avatar') : null,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
