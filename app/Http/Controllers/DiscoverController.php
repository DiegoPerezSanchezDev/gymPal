<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DiscoverController extends Controller
{
    /**
     * Muestra la página de descubrimiento con usuarios, filtros y sugerencias.
     */
    public function index(Request $request)
    {
        // Simulate latency in development for testing Skeletons
        if (app()->environment('local') && request()->has('simulate_latency')) {
            sleep(1);
        }

        // 1. DATOS DEL USUARIO ACTUAL
        // Aseguramos la carga de intereses del usuario actual.
        $usuarioActual = Auth::user() ? User::with('fitnessInterests')->findOrFail(Auth::id()) : null;

        $myConnections = collect();
        if ($usuarioActual) {
            // Obtenemos TODAS las conexiones relevantes (enviadas y recibidas) y las indexamos por el ID del OTRO usuario
            $sent = \App\Models\Connection::where('sender_id', $usuarioActual->id)->get()->keyBy('receiver_id');
            $received = \App\Models\Connection::where('receiver_id', $usuarioActual->id)->get()->keyBy('sender_id');
            $myConnections = $sent->union($received);
        }

        // 2. QUERY BUILDER PRINCIPAL - INICIAMOS LA CONSULTA
        // Aplicamos aquí las condiciones que deben cumplirse SIEMPRE.
        $usersQuery = User::query()->with(['fitnessInterests', 'lookingForInterest']);

        if ($usuarioActual) {
            $usersQuery->where('users.id', '!=', $usuarioActual->id);
        }
        
        // Variable para controlar el estado de los filtros
        $appliedSpecificFilter = false;
        $searchedInterestId = $request->input('interest_id', null);

        // 3. LÓGICA DE FILTROS
        if ($request->filled('lat') && $request->filled('lon')) {
            // Prioridad 1: Búsqueda por Geolocalización
            $lat = $request->input('lat');
            $lon = $request->input('lon');

            $usersQuery->selectRaw("users.*, ( 6371 * acos( cos( radians(?) ) *
                                    cos( radians( latitude ) )
                                    * cos( radians( longitude ) - radians(?)
                                    ) + sin( radians(?) ) *
                                sin( radians( latitude ) ) )
                                ) AS distance", [$lat, $lon, $lat])
                        ->whereNotNull(['latitude', 'longitude'])
                        ->orderBy("distance", 'asc');
            $appliedSpecificFilter = true;

        } elseif ($request->filled('filtro_rapido')) {
            // Prioridad 2: Filtros Rápidos
            $filtroRapido = $request->input('filtro_rapido');

            switch ($filtroRapido) {
                case 'mas_activos':
                    $usersQuery->whereNotNull('last_activity_at')->orderBy('last_activity_at', 'desc');
                    break;
                
                    case 'nuevos_en_ciudad':
                        // Este filtro SÓLO tiene sentido si el usuario actual tiene una ciudad definida.
                        if ($usuarioActual && $usuarioActual->location_city) {
                            $ciudad = strtolower(trim($usuarioActual->location_city));
                            
                            $usersQuery->whereNotNull('location_city')
                                    ->whereRaw('LOWER(location_city) = ?', [$ciudad])
                                    ->where('created_at', '>=', now()->subDays(30));
    
                            // Paso 2: Si además tenemos coordenadas, calculamos distancia y la usamos para ordenar.
                            if ($usuarioActual->latitude && $usuarioActual->longitude) {
                                $lat = $usuarioActual->latitude;
                                $lon = $usuarioActual->longitude;
                                
                                $usersQuery->selectRaw("users.*, ( 6371 * acos( cos( radians(?) ) *
                                                            cos( radians( latitude ) ) *
                                                            cos( radians( longitude ) - radians(?) ) +
                                                            sin( radians(?) ) * sin( radians( latitude ) ) )
                                                        ) AS distance", [$lat, $lon, $lat])
                                        ->whereNotNull(['latitude', 'longitude'])
                                        ->orderBy('distance', 'asc');
                            } else {
                                $usersQuery->orderBy('created_at', 'desc');
                            }
                        } else {
                            $usersQuery->whereRaw('1 = 0');
                        }
                        break;
                
                case 'buscando_companero':
                    $usersQuery->whereNotNull('looking_for_interest_id');
                    
                    if ($usuarioActual) {
                        $ciudad = $usuarioActual->location_city ? strtolower(trim($usuarioActual->location_city)) : null;
                        
                        if ($usuarioActual->latitude && $usuarioActual->longitude) {
                            $usersQuery->selectRaw("users.*,
                                    CASE WHEN looking_for_interest_id = ? THEN 1 ELSE 0 END AS is_matching_interest,
                                    CASE WHEN LOWER(location_city) = ? THEN 1 ELSE 0 END AS is_in_my_city,
                                    ( 6371 * acos( cos( radians(?) ) *
                                        cos( radians( latitude ) ) *
                                        cos( radians( longitude ) - radians(?) ) +
                                        sin( radians(?) ) * sin( radians( latitude ) )
                                    ) ) AS distance", [$searchedInterestId, $ciudad, $usuarioActual->latitude, $usuarioActual->longitude, $usuarioActual->latitude])
                                    ->whereNotNull(['latitude', 'longitude'])
                                    ->orderBy('is_in_my_city', 'desc')
                                    ->orderBy('is_matching_interest', 'desc')
                                    ->orderBy('distance', 'asc');
                        } else {
                            // Fallback: ordenar por coincidencia de interés y ciudad sin geolocalización
                            $usersQuery->selectRaw("users.*,
                                    CASE WHEN looking_for_interest_id = ? THEN 1 ELSE 0 END AS is_matching_interest,
                                    CASE WHEN LOWER(location_city) = ? THEN 1 ELSE 0 END AS is_in_my_city", 
                                    [$searchedInterestId, $ciudad])
                                    ->orderBy('is_in_my_city', 'desc')
                                    ->orderBy('is_matching_interest', 'desc')
                                    ->orderBy('created_at', 'desc');
                        }
                    } else {
                        $usersQuery->orderBy('created_at', 'desc');
                    }
                    break;
            }
            $appliedSpecificFilter = true;

        } else {
            // Prioridad 3: Filtros Manuales o vista por defecto
            $filtrosManualesAplicados = false;
            
            if ($request->filled('search')) {
                $filtrosManualesAplicados = true;
                $searchTerm = $request->input('search');
                $usersQuery->where(fn($q) => $q->where('name', 'like', "%{$searchTerm}%")->orWhere('username', 'like', "%{$searchTerm}%"));
            }
            if ($request->filled('city')) {
                $filtrosManualesAplicados = true;
                $usersQuery->where('location_city', 'like', "%{$request->input('city')}%");
            }
            if ($request->filled('experience_level')) {
                $filtrosManualesAplicados = true;
                $usersQuery->where('experience_level', $request->input('experience_level'));
            }
            if ($request->filled('interests')) {
                $filtrosManualesAplicados = true;
                $usersQuery->whereHas('fitnessInterests', fn($q) => $q->whereIn('fitness_interests.id', $request->input('interests')));
            }
            if ($request->filled('availability_general')) {
                $filtrosManualesAplicados = true;
                $availability = $request->input('availability_general', []);
                $usersQuery->where(function ($query) use ($availability) {
                    foreach ($availability as $slot) {
                        $query->orWhereJsonContains('availability_general', $slot);
                    }
                });
            }

            // Si no se aplicó ningún filtro, usamos el ALGORITMO DE RECOMENDACIÓN
            if (!$appliedSpecificFilter && !$filtrosManualesAplicados && $usuarioActual) {
                
                // 1. Preparar datos de intereses para el cálculo
                $myInterestIds = $usuarioActual->fitnessInterests->pluck('id')->toArray();
                $idsString = !empty($myInterestIds) ? implode(',', $myInterestIds) : '0';

                // 2. Calcular coincidencias de intereses en SQL
                if (!empty($myInterestIds)) {
                    $usersQuery->selectRaw("users.*, 
                        (SELECT COUNT(*) FROM fitness_interest_user WHERE fitness_interest_user.user_id = users.id AND fitness_interest_user.fitness_interest_id IN ($idsString)) as interest_matches
                    ");
                } else {
                    $usersQuery->selectRaw("users.*, 0 as interest_matches");
                }

                // 3. ORDENACIÓN JERÁRQUICA
                // Nivel 1: Mi Ciudad (Prioridad Absoluta)
                if ($usuarioActual->location_city) {
                    $city = strtolower(trim($usuarioActual->location_city));
                    $usersQuery->orderByRaw("CASE WHEN LOWER(location_city) = ? THEN 1 ELSE 0 END DESC", [$city]);
                }

                // Nivel 2: Afinidad (Más intereses en común primero)
                $usersQuery->orderBy('interest_matches', 'desc');

                // Nivel 3: Actividad reciente
                $usersQuery->orderBy('last_activity_at', 'desc');

            } elseif (!$appliedSpecificFilter && !$filtrosManualesAplicados) {
                // Fallback para usuarios sin sesión
                $usersQuery->orderBy('last_activity_at', 'desc');
            }
        }

        // 4. PAGINACIÓN Y POST-PROCESAMIENTO: CÁLCULO DE AFINIDAD
        $users = $usersQuery->paginate(15)->through(function ($user) use ($usuarioActual, $myConnections) {
            // Carga explícita de la relación para evitar problemas de serialización en Inertia.
            $user->load('fitnessInterests', 'lookingForInterest');
            
            if (!$usuarioActual) {
                $user->affinity_score = 0;
                $user->common_interests_ids = [];
                $user->common_availability = [];
                return $user;
            }

            $interesesUsuarioActualIds = $usuarioActual->fitnessInterests->pluck('id');
            $disponibilidadUsuarioActual = (array) $usuarioActual->availability_general;
            $ciudadUsuarioActual = $usuarioActual->location_city;

            $userInterestsIds = $user->fitnessInterests->pluck('id');

            $interesesComunesIds = $interesesUsuarioActualIds->intersect($userInterestsIds);
            $scoreIntereses = $interesesUsuarioActualIds->count() > 0 ? ($interesesComunesIds->count() / $interesesUsuarioActualIds->count()) * 40 : 0;

            $disponibilidadComun = array_intersect($disponibilidadUsuarioActual, (array) $user->availability_general);
            $scoreDisponibilidad = count($disponibilidadUsuarioActual) > 0 ? (count($disponibilidadComun) / count($disponibilidadUsuarioActual)) * 20 : 0;

            $scoreCiudad = ($ciudadUsuarioActual && $user->location_city && strtolower($ciudadUsuarioActual) === strtolower($user->location_city)) ? 40 : 0;

            $user->affinity_score = round($scoreIntereses + $scoreDisponibilidad + $scoreCiudad);
            $user->common_availability = array_values($disponibilidadComun);

            $user->common_interests_ids = $interesesComunesIds->values()->all();

            $user->connection_status = 'none'; // Estado por defecto
            if ($myConnections->has($user->id)) {
                $connection = $myConnections->get($user->id);
                if ($connection->status === 'pending') {
                    // Si la conexión está pendiente, necesitamos saber si la envié yo o me la enviaron a mí
                    $user->connection_status = ($connection->sender_id === $usuarioActual->id) ? 'sent' : 'received';
                } else {
                    $user->connection_status = $connection->status;
                }
            }

            return $user;
        })->withQueryString();

        // 5. RENDERIZADO DE LA VISTA INERTIA
        return Inertia::render('Discover', [
            'title' => 'Conectar con GymPals',
            'users' => $users,
            'user' => $usuarioActual, // Usuario actual para usar su looking_for_interest_id
            'sugerencias' => collect(), // Se mantiene la variable, aunque no se use en la lógica actual.
            'filters' => $request->only(['search', 'city', 'interests', 'availability_general', 'filtro_rapido', 'experience_level', 'lat', 'lon', 'interest_id']),
            'interests' => \App\Models\FitnessInterest::all(['id', 'name']),
            'searchedInterestId' => $searchedInterestId,
        ]);
    }
}