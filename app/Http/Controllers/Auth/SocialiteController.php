<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Error al iniciar sesión con Google.');
        }

        // Buscar usuario por email o google_id
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Usuario existe: Actualizar google_id por si acaso y avatar
            if (!$user->google_id) {
                $user->google_id = $googleUser->getId();
            }
            // Opcional: Actualizar foto si no tiene una personalizada
            // $user->profile_picture_url = $googleUser->getAvatar();
            $user->save();
        } else {
            // Usuario NO existe: Crear uno nuevo
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'username' => 'user_' . Str::random(8), // Generar username temporal
                'password' => Hash::make(Str::random(16)), // Password aleatorio seguro
                'profile_picture_url' => $googleUser->getAvatar(),
                'onboarding_completed' => false, // IMPORTANTE: Enviarlo al onboarding
            ]);
        }

        // Iniciar sesión
        Auth::login($user);

        // Redirigir según estado
        if (!$user->onboarding_completed) {
            return redirect()->route('onboarding.index');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
