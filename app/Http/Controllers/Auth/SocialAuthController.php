<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
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
            return redirect()->route('login')->with('error', 'Error al autenticar con Google.');
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            // Registro automático si no existe
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'username' => Str::slug($googleUser->getName()) . (string)rand(1000,9999), // Username único
                'password' => bcrypt(Str::random(16)), // Contraseña aleatoria segura
                'google_id' => $googleUser->getId(),
                'profile_picture_url' => $googleUser->getAvatar(),
                'email_verified_at' => now(), // Verificado por Google
            ]);
        } else {
            // Actualizar google_id si ya existía el email pero no vinculado
            if (empty($user->google_id)) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'profile_picture_url' => $user->profile_picture_url ?? $googleUser->getAvatar()
                ]);
            }
        }

        Auth::login($user);

        // Redirect to onboarding if not completed
        if (!$user->onboarding_completed) {
            return redirect()->route('onboarding.index');
        }

        return redirect()->intended(route('feed.index'));
    }
}
