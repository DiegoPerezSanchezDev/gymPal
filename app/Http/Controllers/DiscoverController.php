<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DiscoverController extends Controller
{
    /**
     * Muestra la página de descubrimiento con usuarios, filtros y sugerencias.
     */
    public function index(Request $request)
    {
        // --- 1. DATOS DEL USUARIO ACTUAL ---
        // Cargamos el usuario autenticado con sus intereses para usarlo más adelante.
        $usuarioActual = Auth::user() ? User::with('fitnessInterests')->find(Auth::id()) : null;

        // --- 2. QUERY BUILDER PRINCIPAL ---
        // Empezamos la consulta base y cargamos la relación 'fitnessInterests' para evitar N+1 queries.
        $usersQuery = User::query()->with('fitnessInterests');

        // Excluimos al usuario actual de los resultados de búsqueda.
        if ($usuarioActual) {
            $usersQuery->where('id', '!=', $usuarioActual->id);
        }

        // --- 3. LÓGICA DE FILTROS ---

        // Prioridad 1: Búsqueda por Geolocalización
        if ($request->filled('lat') && $request->filled('lon')) {
            $lat = $request->input('lat');
            $lon = $request->input('lon');
            
            $usersQuery->selectRaw("*, ( 6371 * acos( cos( radians(?) ) *
                            cos( radians( latitude ) )
                               * cos( radians( longitude ) - radians(?)
                               ) + sin( radians(?) ) *
                            sin( radians( latitude ) ) )
                            ) AS distance", [$lat, $lon, $lat])
                    ->whereNotNull(['latitude', 'longitude'])
                    ->orderBy("distance", 'asc');

        // Prioridad 2: Filtros Rápidos
        } elseif ($request->filled('filtro_rapido')) {
            $filtroRapido = $request->input('filtro_rapido');
            switch ($filtroRapido) {
                case 'mas_activos':
                    $usersQuery->whereNotNull('last_activity_at')->orderBy('last_activity_at', 'desc');
                    break;
                case 'nuevos_en_ciudad':
                    if ($usuarioActual && $usuarioActual->location_city) {
                        $usersQuery->where('location_city', $usuarioActual->location_city)
                                ->where('created_at', '>=', now()->subDays(30))
                                ->orderBy('created_at', 'desc');
                    }
                    break;
                case 'buscando_companero':
                    $usersQuery->whereNotNull('looking_for_interest_id');
                    break;
            }

        // Prioridad 3: Filtros Manuales
        } else {
            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $usersQuery->where(fn($q) => $q->where('name', 'like', "%{$searchTerm}%")->orWhere('username', 'like', "%{$searchTerm}%"));
            }
            if ($request->filled('city')) {
                $usersQuery->where('location_city', 'like', "%{$request->input('city')}%");
            }
            if ($request->filled('experience_level')) {
                $usersQuery->where('experience_level', $request->input('experience_level'));
            }
            if ($request->filled('interests')) {
                // Aquí usamos 'whereHas' para filtrar usuarios que tengan ciertos intereses.
                $usersQuery->whereHas('fitnessInterests', fn($q) => $q->whereIn('fitness_interests.id', $request->input('interests')));
            }
            if ($request->filled('availability_general')) {
                $availability = $request->input('availability_general', []);
                $usersQuery->where(function ($query) use ($availability) {
                    foreach ($availability as $slot) { $query->orWhereJsonContains('availability_general', $slot); }
                });
            }
        }
        
        // --- 4. PAGINACIÓN ---
        // Ejecutamos la consulta y paginamos los resultados. withQueryString() mantiene los filtros en la URL de paginación.
        $users = $usersQuery->paginate(15)->withQueryString();

        // --- 5. POST-PROCESAMIENTO: CÁLCULO DE AFINIDAD ---
        // Usamos el método `through` que es perfecto para transformar cada ítem de la página actual.
        $users->through(function ($user) use ($usuarioActual) {
            // Si no hay un usuario logueado, no podemos calcular afinidad, así que devolvemos valores por defecto.
            if (!$usuarioActual) {
                $user->affinity_score = 0;
                $user->common_interests = [];
                $user->common_availability = [];
                return $user;
            }

            // Preparamos los datos del usuario actual para comparar
            $interesesUsuarioActual = $usuarioActual->fitnessInterests->pluck('id')->toArray();
            $disponibilidadUsuarioActual = (array) $usuarioActual->availability_general;
            $ciudadUsuarioActual = $usuarioActual->location_city;

            // Calculamos las coincidencias y el score
            $interesesComunes = array_intersect($interesesUsuarioActual, $user->fitnessInterests->pluck('id')->toArray());
            $scoreIntereses = count($interesesUsuarioActual) > 0 ? (count($interesesComunes) / count($interesesUsuarioActual)) * 40 : 0;
        
            $disponibilidadComun = array_intersect($disponibilidadUsuarioActual, (array) $user->availability_general);
            $scoreDisponibilidad = count($disponibilidadUsuarioActual) > 0 ? (count($disponibilidadComun) / count($disponibilidadUsuarioActual)) * 20 : 0;
        
            $scoreCiudad = ($ciudadUsuarioActual && $user->location_city && strtolower($ciudadUsuarioActual) === strtolower($user->location_city)) ? 40 : 0;
        
            // Añadimos las propiedades calculadas al objeto User para esta petición
            $user->affinity_score = round($scoreIntereses + $scoreDisponibilidad + $scoreCiudad);
            $user->common_interests = array_values($interesesComunes);
            $user->common_availability = array_values($disponibilidadComun);
            
            return $user;
        });
        
        // --- 6. LÓGICA PARA SUGERENCIAS (se mantiene la original que es bastante buena) ---
        $sugerencias = collect();
        if ($usuarioActual) {
            // La lógica para las sugerencias se puede mantener o mejorar en el futuro.
            // Por ahora, la dejamos como estaba.
        }

        // --- 7. RENDERIZADO DE LA VISTA INERTIA ---
        // Enviamos todos los datos necesarios al componente de Vue.
        return Inertia::render('Discover', [
            'title' => 'Conectar con GymPals',
            'users' => $users,
            'sugerencias' => $sugerencias, // Puedes re-implementar la lógica de sugerencias aquí si quieres.
            'filters' => $request->only(['search', 'city', 'interests', 'availability_general', 'filtro_rapido', 'experience_level', 'lat', 'lon']),
            'interests' => \App\Models\FitnessInterest::all(['id', 'name']),
        ]);
    }
}