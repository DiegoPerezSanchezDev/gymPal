<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function toggleFollow(User $userToToggle) // Route Model Binding por ID (o username si lo configuras)
    {
        $currentUser = Auth::user();

        if ($currentUser->id === $userToToggle->id) {
            return back()->with('error_toast', 'No puedes seguirte a ti mismo.');
        }

        // Intentar cambiar el estado de seguimiento
        // toggle() en una relación BelongsToMany sincroniza el estado (añade si no existe, quita si existe)
        $currentUser->following()->toggle($userToToggle->id);

        // Determinar el nuevo estado para devolverlo (opcional, pero útil para el frontend)
        $isNowFollowing = $currentUser->isFollowing($userToToggle);

        // Devolver con un mensaje flash para el toast y el nuevo estado
        $message = $isNowFollowing ? 'Ahora sigues a ' . $userToToggle->name : 'Has dejado de seguir a ' . $userToToggle->name;

        // Cuando Inertia hace una petición POST/PUT/PATCH/DELETE, espera una redirección.
        // Si quieres que la prop 'isFollowing' se actualice en la página actual sin recarga completa:
        return back()->with([
            'success_toast' => $message,
            // 'isFollowing' => $isNowFollowing, // Esto no actualiza la prop directamente.
                                                // La mejor forma es que el componente Profile/ShowPublic
                                                // recargue sus datos o que el controlador que renderiza
                                                // Profile/ShowPublic recalcule isFollowing.
                                                // Por ahora, el frontend invertirá el estado localmente
                                                // y el backend lo confirma.
        ]);
    }
}
