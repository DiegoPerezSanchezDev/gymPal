<?php

namespace App\Http\Controllers;

use App\Models\Gym;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GymController extends Controller
{
    /**
     * Unir al usuario actual a un gimnasio.
     */
    public function join(Request $request, Gym $gym)
    {
        $user = Auth::user();

        if (!$user->gyms()->where('gym_id', $gym->id)->exists()) {
            $user->gyms()->attach($gym->id);
            $gym->increment('users_count'); // Mantenemos el contador desnormalizado para rendimiento rápido
        }

        return back()->with('success', '¡Te has unido al gimnasio!');
    }

    /**
     * Salir de un gimnasio.
     */
    public function leave(Request $request, Gym $gym)
    {
        $user = Auth::user();

        if ($user->gyms()->where('gym_id', $gym->id)->exists()) {
            $user->gyms()->detach($gym->id);
            $gym->decrement('users_count');
        }

        return back()->with('success', 'Has dejado el gimnasio.');
    }

    /**
     * Mostrar detalles del gimnasio (para el modal/página).
     */
    public function show(Gym $gym)
    {
        $gym->load(['users' => function($query) {
            $query->limit(12)->inRandomOrder(); // Solo mostramos algunos para "preview"
        }]);
        
        // Verificar si el usuario actual es miembro
        $isMember = Auth::check() ? Auth::user()->gyms()->where('gym_id', $gym->id)->exists() : false;

        // Devolvemos JSON si es una petición AJAX (desde el mapa)
        if (request()->wantsJson()) {
            return response()->json([
                'gym' => $gym,
                'members' => $gym->users->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username,
                    'profile_picture_url' => $u->profile_picture_url,
                ]),
                'is_member' => $isMember
            ]);
        }

        // Si fuera una página completa, aquí iría el Inertia render
        // return Inertia::render('Gyms/Show', ...);
    }
}
