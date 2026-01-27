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
        try {
            $gym->load(['users' => function($query) {
                $query->limit(12)->inRandomOrder(); // Solo mostramos algunos para "preview"
            }]);
            
            // Verificar si el usuario actual es miembro
            $isMember = Auth::check() ? Auth::user()->gyms()->where('gym_id', $gym->id)->exists() : false;

            $currentUser = Auth::user();
        $followingMeIds = $currentUser ? \App\Models\Connection::where('receiver_id', $currentUser->id)->where('status', 'accepted')->pluck('sender_id')->toArray() : [];

            return response()->json([
                'gym' => $gym,
                'members' => $gym->users->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username,
                    'profile_picture_url' => $u->profile_picture_url,
                    'is_following_me' => in_array($u->id, $followingMeIds),
                ]),
                'is_member' => $isMember
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in GymController@show: ' . $e->getMessage());
            return response()->json(['error' => 'Error loading gym details'], 500);
        }
    }

    /**
     * Obtener todos los miembros de un gimnasio (paginado).
     */
    public function members(Gym $gym)
    {
        $currentUser = Auth::user();
        $followingMeIds = $currentUser ? \App\Models\Connection::where('receiver_id', $currentUser->id)->where('status', 'accepted')->pluck('sender_id')->toArray() : [];

        $members = $gym->users()
            ->select('users.id', 'users.name', 'users.username', 'users.profile_picture_url')
            ->paginate(40)
            ->through(function ($u) use ($followingMeIds) {
                $u->is_following_me = in_array($u->id, $followingMeIds);
                return $u;
            });

        return response()->json($members);
    }
}
