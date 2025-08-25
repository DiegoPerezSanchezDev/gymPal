<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user(); // Obtener el usuario una vez

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'display_name' => $user->display_name,
                    'profile_picture_url' => $user->profile_picture_url,
                    'bio' => $user->bio,
                    'location_city' => $user->location_city,
                    'availability_general' => $user->availability_general, // Asume que esto es un atributo directo o ya procesado
                    'experience_level' => $user->experience_level,
                    
                    // Cargar la relación fitnessInterests si el usuario existe
                    'fitness_interests' => $user->loadMissing('fitnessInterests')->fitnessInterests
                ] : null,
            ],
            'flash' => [
                'success_toast' => fn () => $request->session()->get('success_toast'),
                'error_toast' => fn () => $request->session()->get('error_toast'),
                'info_toast' => fn () => $request->session()->get('info_toast'),
                'warning_toast' => fn () => $request->session()->get('warning_toast'),
            ],
            'geoapify_key' => config('services.geoapify.key'),
        ]);
    }
}
