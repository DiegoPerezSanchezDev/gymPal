<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UpdateUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
        public function handle(Request $request, Closure $next): Response
    {
        // 1. Verificamos si hay un usuario autenticado.
        if (Auth::check()) {
            
            // --- LA CORRECCIÓN CLAVE ---
            // En lugar de usar Auth::user() directamente, usamos el ID del usuario
            // para buscar una instancia fresca del modelo de Eloquent desde la base de datos.
            // Esto garantiza que siempre tengamos un objeto con el método save().
            $user = User::find(Auth::id());

            // Una comprobación extra por seguridad: si el usuario existe en la BD...
            if ($user) {
                // Actualizamos el campo con la fecha y hora actual.
                $user->last_activity_at = now();
                
                // Ahora, ->save() funcionará porque $user es una instancia de Eloquent.
                $user->save();
            }
        }

        // 5. Dejamos que la petición continúe su camino normal.
        return $next($request);
    }
}
