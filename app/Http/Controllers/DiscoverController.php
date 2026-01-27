<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Gym;
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
        $iFollowIds = collect(); // IDs de usuarios que YO sigo
        
        if ($usuarioActual) {
            // Obtenemos TODAS las conexiones relevantes (enviadas y recibidas) y las indexamos por el ID del OTRO usuario
            $sent = \App\Models\Connection::where('sender_id', $usuarioActual->id)->get()->keyBy('receiver_id');
            $received = \App\Models\Connection::where('receiver_id', $usuarioActual->id)->get()->keyBy('sender_id');
            $myConnections = $sent->union($received);
            
            // IDs de personas que YO sigo (conexiones que YO envié y fueron aceptadas)
            $iFollowIds = \App\Models\Connection::where('sender_id', $usuarioActual->id)
                ->where('status', 'accepted')
                ->pluck('receiver_id');
        }

        // 2. QUERY BUILDER PRINCIPAL - INICIAMOS LA CONSULTA
        // Aplicamos aquí las condiciones que deben cumplirse SIEMPRE.
        $usersQuery = User::query()->with(['fitnessInterests', 'lookingForInterest']);

        if ($usuarioActual) {
            $usersQuery->where('users.id', '!=', $usuarioActual->id);
            
            // EXCLUIR usuarios que YO ya sigo (pero permitir los que ME siguen)
            if ($iFollowIds->isNotEmpty()) {
                $usersQuery->whereNotIn('users.id', $iFollowIds);
            }
        }
        
        // Variable para controlar el estado de los filtros
        $appliedSpecificFilter = false;
        $searchedInterestId = $request->input('interest_id');
        $interests = $request->input('interests', []);
        
        // 3. APLICAR FILTROS MANUALES (GLOBALES)
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
        
        if (!empty($interests)) {
            $usersQuery->whereHas('fitnessInterests', fn($q) => $q->whereIn('fitness_interests.id', $interests));
        }

        if ($request->filled('availability_general')) {
            $availability = $request->input('availability_general', []);
            $usersQuery->where(function ($query) use ($availability) {
                foreach ($availability as $slot) {
                    $query->orWhereJsonContains('availability_general', $slot);
                }
            });
        }

        if ($request->filled('gym_id')) {
            $usersQuery->whereHas('gyms', fn($q) => $q->where('gyms.id', $request->input('gym_id')));
        }

        // 4. LÓGICA DE ORDENACIÓN Y FILTROS RÁPIDOS
        if ($request->filled('lat') && $request->filled('lon')) {
            // Caso: Ubicación por GPS
            $lat = $request->input('lat');
            $lon = $request->input('lon');

            $usersQuery->selectRaw("users.*, ( 6371 * acos( cos( radians(?) ) *
                                    cos( radians( latitude ) )
                                    * cos( radians( longitude ) - radians(?)
                                    ) + sin( radians(?) ) *
                                sin( radians( latitude ) ) )
                                ) AS distance", [$lat, $lon, $lat])
                        ->whereNotNull('latitude')
                        ->whereNotNull('longitude')
                        ->where('latitude', '!=', 0)
                        ->where('longitude', '!=', 0)
                        ->whereRaw("( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) <= 20", [$lat, $lon, $lat])
                        ->orderBy("distance", 'asc');
            $appliedSpecificFilter = true;

        } elseif ($request->filled('filtro_rapido')) {
            // Caso: Filtros Rápidos
            $filtroRapido = $request->input('filtro_rapido');

            switch ($filtroRapido) {
                case 'mas_activos':
                    $usersQuery->whereNotNull('last_activity_at')->orderBy('last_activity_at', 'desc');
                    break;
                
                case 'nuevos_en_ciudad':
                    if ($usuarioActual && $usuarioActual->location_city) {
                        $ciudad = strtolower(trim($usuarioActual->location_city));
                        $usersQuery->whereNotNull('location_city')
                                ->whereRaw('LOWER(location_city) = ?', [$ciudad])
                                ->where('created_at', '>=', now()->subDays(15)); // Rango de 15 días
                        
                        if ($usuarioActual->latitude && $usuarioActual->longitude && $usuarioActual->latitude != 0) {
                            $usersQuery->selectRaw("users.*, ( 6371 * acos( cos( radians(?) ) *
                                                            cos( radians( latitude ) ) *
                                                            cos( radians( longitude ) - radians(?) ) +
                                                            sin( radians(?) ) * sin( radians( latitude ) ) )
                                                        ) AS distance", [$usuarioActual->latitude, $usuarioActual->longitude, $usuarioActual->latitude])
                                    ->whereNotNull('latitude')
                                    ->whereNotNull('longitude')
                                    ->where('latitude', '!=', 0)
                                    ->where('longitude', '!=', 0)
                                    ->whereRaw("( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) <= 20", [$usuarioActual->latitude, $usuarioActual->longitude, $usuarioActual->latitude])
                                    ->orderBy('distance', 'asc');
                        } else {
                            $usersQuery->orderBy('created_at', 'desc');
                        }
                    }
                    break;
                
                case 'buscando_companero':
                    $usersQuery->whereNotNull('looking_for_interest_id');
                    if ($searchedInterestId) {
                        $usersQuery->where('looking_for_interest_id', $searchedInterestId);
                    }
                    $usersQuery->orderBy('created_at', 'desc');
                    break;
            }
            $appliedSpecificFilter = true;

        } else {
            // Caso: Algoritmo de recomendación por defecto
            if ($usuarioActual) {
                $myInterestIds = $usuarioActual->fitnessInterests->pluck('id')->toArray();
                $idsString = !empty($myInterestIds) ? implode(',', $myInterestIds) : '0';

                $usersQuery->selectRaw("users.*, 
                    (SELECT COUNT(*) FROM fitness_interest_user WHERE fitness_interest_user.user_id = users.id AND fitness_interest_user.fitness_interest_id IN ($idsString)) as interest_matches
                ");

                if ($usuarioActual->location_city) {
                    $city = strtolower(trim($usuarioActual->location_city));
                    $usersQuery->orderByRaw("CASE WHEN LOWER(location_city) = ? THEN 1 ELSE 0 END DESC", [$city]);
                }
                $usersQuery->orderBy('interest_matches', 'desc');
                $usersQuery->orderBy('last_activity_at', 'desc');
            } else {
                $usersQuery->orderBy('last_activity_at', 'desc');
            }
        }

        // IDs de las personas que siguen al usuario autenticado para la etiqueta "Te sigue"
        $followingMeIds = $usuarioActual ? \App\Models\Connection::where('receiver_id', $usuarioActual->id)
            ->where('status', 'accepted')
            ->pluck('sender_id')
            ->toArray() : [];

        // 4. PAGINACIÓN Y POST-PROCESAMIENTO: CÁLCULO DE AFINIDAD
        $users = $usersQuery->paginate(15)->through(function ($user) use ($usuarioActual, $myConnections, $followingMeIds) {
            // Carga explícita de la relación para evitar problemas de serialización en Inertia.
            $user->load('fitnessInterests', 'lookingForInterest');
            
            $user->is_following_me = in_array($user->id, $followingMeIds);

            if (!$usuarioActual) {
                $user->affinity_score = 0;
                $user->common_interests_ids = [];
                $user->common_availability = [];
                return $user;
            }

            // SEGURIDAD / PRIVACIDAD: Location Fuzzing
            // Añadimos un pequeño error aleatorio (+/- 300-400m) a las coordenadas para no revelar la casa exacta.
            if ($user->latitude && $user->longitude) {
                // 0.004 grados son aprox ~440m.
                $fuzzLat = mt_rand(-40, 40) / 10000; 
                $fuzzLon = mt_rand(-40, 40) / 10000;
                
                $user->latitude = (float)$user->latitude + $fuzzLat;
                $user->longitude = (float)$user->longitude + $fuzzLon;
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
            'filters' => $request->only(['search', 'city', 'interests', 'availability_general', 'filtro_rapido', 'experience_level', 'lat', 'lon', 'interest_id', 'gym_id']),
            'interests' => \App\Models\FitnessInterest::all(['id', 'name']),
            'searchedInterestId' => $searchedInterestId,
            'initialGyms' => $this->getInitialGyms($request),
            'geoapify_key' => config('services.geoapify.key'),
            'gymsInCity' => $usuarioActual && $usuarioActual->location_city 
                ? Gym::where(function($query) use ($usuarioActual) {
                      $city = strtolower(trim($usuarioActual->location_city));
                      $query->whereRaw('LOWER(address) LIKE ?', ["%{$city}%"])
                            ->orWhereRaw('LOWER(name) LIKE ?', ["%{$city}%"]);
                  })
                  ->select('id', 'name', 'address')
                  ->when($usuarioActual->latitude && $usuarioActual->longitude, function($query) use ($usuarioActual) {
                        // Si la búsqueda por texto falla (o para complementar), añadir cercanía si es posible
                        // En este caso, hacemos un UNION o simplemente priorizamos la query visual.
                        // Para simplificar: Si no hay resultados por texto, buscar por distancia.
                        // Pero Eloquent builder es único.
                        // Vamos a usar una lógica híbrida: Buscar por texto, y si la colección es pequeña (<5), añadir cercanos.
                        // Implicaría hacerlo fuera del builder. Para mantener builder simple:
                        return $query;
                  })
                  ->limit(50)->get()
                  ->whenEmpty(function($collection) use ($usuarioActual) {
                        if ($usuarioActual->latitude && $usuarioActual->longitude) {
                            $lat = $usuarioActual->latitude;
                            $lon = $usuarioActual->longitude;
                            
                            $queryLat = (float) $lat;
                            $queryLon = (float) $lon;
                            
                            $subQuery = \DB::table('gyms')
                                ->select('*')
                                ->selectRaw("(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$queryLat, $queryLon, $queryLat])
                                ->whereNotNull(['latitude', 'longitude']);

                            return Gym::fromSub($subQuery, 'gyms_with_distance')
                                ->where('distance', '<', 50)
                                ->orderBy('distance')
                                ->select('id', 'name', 'address', 'latitude', 'longitude')
                                ->limit(50)
                                ->get();
                        }
                        return $collection;
                   })
                : [],
        ]);
    }

    private function getInitialGyms(Request $request)
    {
        $lat = $request->input('lat');
        $lon = $request->input('lon');

        if (!$lat || !$lon) {
           return []; 
        }

        $queryLat = (float) $lat;
        $queryLon = (float) $lon;

        $subQuery = \DB::table('gyms')
            ->select('*')
            ->selectRaw("(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$queryLat, $queryLon, $queryLat])
            ->whereNotNull(['latitude', 'longitude']);

        return Gym::fromSub($subQuery, 'gyms_with_distance')
            ->where('distance', '<', 15)
            ->orderBy('distance')
            ->limit(100)
            ->get();
    }

    public function nearbyGyms(Request $request)
    {
        // 1. LIMITS (Map View) - Si nos pasan límites explícitos
        if ($request->has(['south', 'west', 'north', 'east'])) {
            $request->validate([
                'south' => 'required|numeric',
                'west' => 'required|numeric',
                'north' => 'required|numeric',
                'east' => 'required|numeric',
            ]);

            return response()->json(
                Gym::whereBetween('latitude', [$request->south, $request->north])
                   ->whereBetween('longitude', [$request->west, $request->east])
                   ->limit(300)
                   ->get()
            );
        }

        // 2. SEARCH PARAMETERS
        $lat = $request->input('lat');
        $lon = $request->input('lon') ?? $request->input('lng');
        $search = $request->input('search');

        // Bounding Box Logic (Aprox +/- 50km)
        // 1 grado latitud ~= 111 km -> 0.45 grados ~= 50km
        $deltaLat = 0.45;
        $deltaLon = 0.45; // Aprox para longitudes medias, suficiente para MVP

        $query = Gym::query();

        if ($lat && $lon) {
            $minLat = $lat - $deltaLat;
            $maxLat = $lat + $deltaLat;
            $minLon = $lon - $deltaLon;
            $maxLon = $lon + $deltaLon;

            $query->whereBetween('latitude', [$minLat, $maxLat])
                  ->whereBetween('longitude', [$minLon, $maxLon]);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                 $q->where('name', 'like', "%{$search}%")
                   ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // 4. FALLBACK: FILTER BY CITY NAME
        // Si nos pasan ciudad pero no (o además de) coordenadas, aseguramos buscar por texto de ciudad
        if ($request->filled('city')) {
             $city = $request->input('city');
             $query->orWhere('address', 'like', "%{$city}%");
        }

        // Si no hay filtro de ubi, ni texto, ni ciudad, no devolver nada
        if (!$lat && !$lon && !$search && !$request->filled('city')) {
            return response()->json(['gyms' => []]);
        }

        $gyms = $query->limit(50)->get();
        
        // Opcional: Calcular distancia precisa en PHP para mostrar "x km" si se quisiera
        // Pero para el selector dropdown no es crítico.

        return response()->json(['gyms' => $gyms]);
    }
}