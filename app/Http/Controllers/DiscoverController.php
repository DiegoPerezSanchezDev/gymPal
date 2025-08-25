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
    $usuarioActualId = Auth::id();
    $usuarioActual = $usuarioActualId ? User::with('fitnessInterests')->find($usuarioActualId) : null;

    // --- 2. QUERY BUILDER PRINCIPAL ---
    $usersQuery = User::query();

    if ($usuarioActualId) {
        $usersQuery->where('id', '!=', $usuarioActualId);
    }

    // --- 3. LÓGICA DE FILTROS ---

    // Prioridad 1: Búsqueda por Geolocalización
    if ($request->filled('lat') && $request->filled('lon')) {
        $lat = $request->input('lat');
        $lon = $request->input('lon');
        $radius = 50; // Radio de búsqueda en kilómetros

        $usersQuery->selectRaw("*, ( 6371 * acos( cos( radians(?) ) *
                        cos( radians( latitude ) )
                           * cos( radians( longitude ) - radians(?)
                           ) + sin( radians(?) ) *
                        sin( radians( latitude ) ) )
                        ) AS distance", [$lat, $lon, $lat])
                ->whereNotNull(['latitude', 'longitude']) // Solo usuarios que tengan coordenadas
                ->having("distance", "<", $radius)
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
                $usersQuery->whereHas('fitnessInterests', fn($q) => $q->whereIn('fitness_interests.id', $request->input('interests')));
            }
            if ($request->filled('availability_general')) {
                $availability = $request->input('availability_general', []);
                $usersQuery->where(function ($query) use ($availability) {
                    foreach ($availability as $slot) { $query->orWhereJsonContains('availability_general', $slot); }
                });
            }
        }
        
        // --- 4. PAGINACIÓN Y CÁLCULO DE AFINIDAD PARA RESULTADOS DE BÚSQUEDA ---
        $users = $usersQuery->with(['fitnessInterests'])->paginate(15)->withQueryString();

        if ($usuarioActual) {
            $interesesUsuarioActual = $usuarioActual->fitnessInterests->pluck('id')->toArray();
            $disponibilidadUsuarioActual = (array) $usuarioActual->availability_general;
            $ciudadUsuarioActual = $usuarioActual->location_city;

            $users->through(function ($user) use ($interesesUsuarioActual, $disponibilidadUsuarioActual, $ciudadUsuarioActual) {
                $interesesComunes = array_intersect($interesesUsuarioActual, $user->fitnessInterests->pluck('id')->toArray());
                $scoreIntereses = count($interesesUsuarioActual) > 0 ? (count($interesesComunes) / count($interesesUsuarioActual)) * 40 : 0;
            
                $disponibilidadComun = array_intersect($disponibilidadUsuarioActual, (array) $user->availability_general);
                $scoreDisponibilidad = count($disponibilidadUsuarioActual) > 0 ? (count($disponibilidadComun) / count($disponibilidadUsuarioActual)) * 20 : 0;
            
                $scoreCiudad = ($ciudadUsuarioActual && $user->location_city && strtolower($ciudadUsuarioActual) === strtolower($user->location_city)) ? 40 : 0;
            
                $user->affinity_score = round($scoreIntereses + $scoreDisponibilidad + $scoreCiudad);
                $user->common_interests = array_values($interesesComunes);
                $user->common_availability = array_values($disponibilidadComun);
                return $user;
            });
        }
        
        // --- 5. LÓGICA PARA SUGERENCIAS PERSONALIZADAS ("Personas que podrían interesarte") ---
        $sugerencias = collect();
        if ($usuarioActual) {
            $candidatosPotenciales = User::query()
                ->with('fitnessInterests')
                ->where('id', '!=', $usuarioActualId)
                ->where('location_city', $usuarioActual->location_city) // Crucial para relevancia
                ->inRandomOrder() // Para no mostrar siempre los mismos
                ->limit(100) // Limitar para mejorar rendimiento
                ->get();

            $candidatosConPuntuacion = $candidatosPotenciales->map(function ($user) use ($interesesUsuarioActual, $disponibilidadUsuarioActual, $ciudadUsuarioActual) {
                // Reutilizamos la misma lógica de cálculo de afinidad
                $interesesComunes = array_intersect($interesesUsuarioActual, $user->fitnessInterests->pluck('id')->toArray());
                $scoreIntereses = count($interesesUsuarioActual) > 0 ? (count($interesesComunes) / count($interesesUsuarioActual)) * 40 : 0;
                $disponibilidadComun = array_intersect($disponibilidadUsuarioActual, (array) $user->availability_general);
                $scoreDisponibilidad = count($disponibilidadUsuarioActual) > 0 ? (count($disponibilidadComun) / count($disponibilidadUsuarioActual)) * 20 : 0;
                $scoreCiudad = ($ciudadUsuarioActual && $user->location_city && strtolower($ciudadUsuarioActual) === strtolower($user->location_city)) ? 40 : 0;
                
                // Añadimos las propiedades calculadas directamente al modelo para esta petición
                $user->affinity_score = round($scoreIntereses + $scoreDisponibilidad + $scoreCiudad);
                $user->common_interests = array_values($interesesComunes);
                $user->common_availability = array_values($disponibilidadComun);
                return $user;
            });

            // Ordenamos por afinidad y tomamos los mejores resultados
            $sugerencias = $candidatosConPuntuacion->where('affinity_score', '>=', 50) // Umbral mínimo de afinidad
                                                ->sortByDesc('affinity_score')
                                                ->take(8); // Número de sugerencias a mostrar
        }

        // --- 6. RENDERIZADO DE LA VISTA INERTIA ---
        return Inertia::render('Discover', [
        'title' => 'Conectar con GymPals',
        'users' => $users,
        'sugerencias' => $sugerencias,
        'filters' => $request->only(['search', 'city', 'interests', 'availability_general', 'filtro_rapido', 'experience_level', 'lat', 'lon']),
        'interests' => \App\Models\FitnessInterest::all(['id', 'name']),
        ]);
    }
}