<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log; // Para logging
use Illuminate\Support\Facades\Storage; // Para manejo de archivos
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Validation\Rule; 
use App\Models\Connection;
class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): \Inertia\Response
    {
        $user = $request->user()->load(['fitnessInterests', 'gyms']);
        
        // Obtener gimnasios relevantes
        $gyms = [];
        if ($user->location_city) {
            $city = strtolower(trim($user->location_city));
            $gyms = \App\Models\Gym::where(function($query) use ($city) {
                  $query->whereRaw('LOWER(address) LIKE ?', ["%{$city}%"])
                        ->orWhereRaw('LOWER(name) LIKE ?', ["%{$city}%"]);
              })->select('id', 'name', 'address', 'latitude', 'longitude')->limit(50)->get();
              
            // Fallback: Si no hay gimnasios por ciudad, buscar por proximidad
            if ($gyms->isEmpty() && $user->latitude && $user->longitude) {
                $lat = $user->latitude;
                $lon = $user->longitude;
                
                $gyms = \App\Models\Gym::fromRaw("(
                    SELECT *, ( 6371 * acos( cos( radians({$lat}) ) *
                        cos( radians( latitude ) )
                        * cos( radians( longitude ) - radians({$lon})
                        ) + sin( radians({$lat}) ) *
                        sin( radians( latitude ) ) )
                    ) AS distance
                    FROM gyms
                    WHERE latitude IS NOT NULL AND longitude IS NOT NULL
                ) AS gyms_with_distance")
                ->where('distance', '<', 50)
                ->orderBy('distance')
                ->select('id', 'name', 'address', 'latitude', 'longitude')
                ->limit(50)
                ->get();
            }
        }

        // Asegurar que los gimnasios actuales del usuario estén en la lista de opciones
        if ($user->gyms->isNotEmpty()) {
            $currentGyms = $user->gyms->map(function($g) {
                return $g->makeHidden('pivot'); 
            });
            
            // Unir y quitar duplicados por ID
            $gyms = collect($gyms)->merge($currentGyms)->unique('id')->values();
        }
        
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'title' => 'Editar Perfil',
            'isLoginPage' => false, // Estas props pueden ser útiles para el layout
            'isRegisterPage' => false,
            'user' => $user, 
            'interests' => \App\Models\FitnessInterest::all(['id', 'name']),
            'geoapify_key' => config('services.geoapify.key'),
            'gyms' => $gyms,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        Log::info('[PERFIL UPDATE] Datos VALIDADOS:', $data);

        // --- MANEJO DE FOTO DE PERFIL ---
        // Eliminar foto si se solicitó
        if (!empty($data['remove_profile_picture']) && $user->profile_picture_url) {
            // Eliminar archivo físico si existe
            if (Storage::disk('public')->exists($user->profile_picture_url)) {
                Storage::disk('public')->delete($user->profile_picture_url);
            }
            $user->profile_picture_url = null;
        }

        // Subir nueva foto si se proporcionó
        if ($request->hasFile('profile_picture')) {
            // Eliminar foto anterior si existe
            if ($user->profile_picture_url && Storage::disk('public')->exists($user->profile_picture_url)) {
                Storage::disk('public')->delete($user->profile_picture_url);
            }

            // Guardar nueva foto
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $user->profile_picture_url = $path;
        }

        // --- MANEJO DE BANNER ---
        // Subir nuevo banner si se proporcionó
        if ($request->hasFile('banner_picture')) {
            // Eliminar banner anterior si existe
            if ($user->banner_picture_url && Storage::disk('public')->exists($user->banner_picture_url)) {
                Storage::disk('public')->delete($user->banner_picture_url);
            }

            // Guardar nuevo banner
            $path = $request->file('banner_picture')->store('banners', 'public');
            $user->banner_picture_url = $path;
        }

        // Actualizar campos simples
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Guardar disponibilidad (array/JSON)
        if (isset($data['availability_general'])) {
            $user->availability_general = $data['availability_general'];
        }

        $user->save();

        // Guardar intereses deportivos (relación muchos a muchos)
        $user->fitnessInterests()->sync($data['interests'] ?? []);

        // Guardar gimnasios (relación muchos a muchos)
        if (isset($data['gym_ids'])) {
            $user->gyms()->sync($data['gym_ids']);
        }

        return Redirect::route('profile.show.public', ['user' => $user->username])
            ->with('success_toast', 'Perfil actualizado correctamente.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Display the specified user's public profile.
     */
    public function showPublic(User $user): \Inertia\Response
    {
        $currentUser = Auth::user();

        $connectionStatus = 'none';
    $connection = null;
    $isFollowingMe = false;

    if ($currentUser && $currentUser->id !== $user->id) {
        // Verificar conexión que YO envié
        $sentConnection = Connection::where('sender_id', $currentUser->id)
            ->where('receiver_id', $user->id)
            ->first();

        // Verificar conexión que ÉL me envió
        $receivedConnection = Connection::where('sender_id', $user->id)
            ->where('receiver_id', $currentUser->id)
            ->first();

        // Determinar si él me sigue (para el badge)
        if ($receivedConnection && $receivedConnection->status === 'accepted') {
            $isFollowingMe = true;
        }

        // Determinar el estado del botón basado en MI conexión
        if ($sentConnection) {
            if ($sentConnection->status === 'pending') {
                $connectionStatus = 'sent';
                $connection = $sentConnection;
            } else if ($sentConnection->status === 'accepted') {
                $connectionStatus = 'accepted';
                $connection = $sentConnection;
            }
        } else if ($receivedConnection && $receivedConnection->status === 'pending') {
            // Si él me envió solicitud pendiente (y yo no le he enviado nada)
            $connectionStatus = 'received';
            $connection = $receivedConnection;
        }
        // Si no hay ninguna conexión mía, el estado es 'none'
    }
        

        $user->load(['fitnessInterests', 'gyms']);

        $user->loadCount('posts');
        
        // Calcular GymPals, Seguidores y Siguiendo
        
        // Calcular GymPals, Seguidores y Siguiendo
        $following = $user->pending_sent; // Usuarios que este perfil sigue (pending)
        $followingAccepted = Connection::where('sender_id', $user->id)
            ->where('status', 'accepted')
            ->with('receiver:id,name,username,profile_picture_url')
            ->get()
            ->pluck('receiver');
        
        $followers = $user->pending_received; // Usuarios que siguen a este perfil (pending)
        $followersAccepted = Connection::where('receiver_id', $user->id)
            ->where('status', 'accepted')
            ->with('sender:id,name,username,profile_picture_url')
            ->get()
            ->pluck('sender');
        
        // GymPals = match mutuo (ambos se siguen con accepted)
        $followingIds = $followingAccepted->pluck('id')->toArray();
        $followersIds = $followersAccepted->pluck('id')->toArray();
        $gymPalsIds = array_intersect($followingIds, $followersIds);
        $gymPals = User::whereIn('id', $gymPalsIds)
            ->select('id', 'name', 'username', 'profile_picture_url')
            ->get();
        
        // Contadores
        $gymPalsCount = count($gymPalsIds);
        $followingCount = $followingAccepted->count();
        $followersCount = $followersAccepted->count();

        // Calcular estadísticas del usuario
        $stats = [
            'workouts_completed' => $user->workoutLogs()->count(),
            'active_days_month' => $user->workoutLogs()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->distinct()
                ->selectRaw('DATE(created_at) as date')
                ->get()
                ->count(),
            'streak_days' => $this->calculateStreak($user),
            'level' => $this->calculateLevel($user),
        ];

        // Cargar posts si es el propio perfil o si están conectados
        $posts = [];
        if (($currentUser && $currentUser->id === $user->id) || $connectionStatus === 'accepted') {
            $posts = $user->posts()
                ->with([
                    'user:id,name,username,profile_picture_url',
                    'comments.user:id,name,username,profile_picture_url',
                    'likers', // Corregido: likes -> likers
                    'workout:id,name,description,difficulty,duration_minutes', // Añadido
                    'workout.exercises:id,workout_id,exercise_name,sets_data,rest_seconds,order' // Añadido
                ])
                ->withCount(['comments', 'likers']) // Corregido: likes -> likers
                ->latest()
                ->get()
                ->map(function ($post) use ($currentUser) {
                    // Corregido: likes -> likers. likers son Usuarios, así que buscamos por id.
                    $post->is_liked = $currentUser ? $post->likers->contains('id', $currentUser->id) : false;
                    
                    // Optimización para likes: obtener los últimos 3 para mostrar avatares
                    // Corregido: likes() -> likers()
                    $post->latest_likers = $post->likers()->latest('post_like.created_at')->take(3)->get();
                    
                    // Optimización para comentarios: obtener los últimos 2
                    $post->latest_comments = $post->comments()->latest()->take(2)->with('user:id,name,username,profile_picture_url')->get()->reverse()->values();
                    return $post;
                });
        }

        // Cargar rutinas si es el propio perfil o si están conectados
        $workouts = [];
        if (($currentUser && $currentUser->id === $user->id) || $connectionStatus === 'accepted') {
            $query = $user->workouts()->with('exercises');
            
            // Si NO es tu propio perfil, solo mostrar públicas
            if (!$currentUser || $currentUser->id !== $user->id) {
                $query->where('is_public', true);
            }
            
            $workouts = $query->latest()->get();
        }

        // Verificar si el usuario actual es GymPal del perfil visitado
        $areGymPals = false;
        if ($currentUser && $currentUser->id !== $user->id) {
            $areGymPals = in_array($currentUser->id, $gymPalsIds);
        }

        return Inertia::render('Profile/ShowPublic', [
            'profileUser' => $user,
            'title' => 'Perfil de ' . $user->name,
            'isOwnProfile' => $currentUser ? $currentUser->id === $user->id : false,
            
            'connection_status' => $connectionStatus,
            'connection_id' => $connection ? $connection->id : null,
            'is_following_me' => $isFollowingMe, // Indica si el perfil visitado me sigue
            'are_gym_pals' => $areGymPals, // Indica si somos GymPals
            
            // Contadores y listas completas
            'gym_pals_count' => $gymPalsCount,
            'followers_count' => $followersCount,
            'following_count' => $followingCount,
            'gym_pals_list' => $gymPals,
            'followers_list' => $followersAccepted->values(), // Lista completa de seguidores
            'following_list' => $followingAccepted->values(), // Lista completa de siguiendo
            
            // Estadísticas del usuario
            'stats' => $stats,
            
            // Gráficas de progreso (V2)
            'progressCharts' => $this->getProfileProgressCharts($user),
            
            'posts' => $posts, // Pasar los posts
            'workouts' => $workouts, // Pasar las rutinas
        ]);
    }

    /**
     * Obtener datos para las gráficas del perfil (V2 Premium)
     */
    private function getProfileProgressCharts(User $user)
    {
        $targetDate = \Carbon\Carbon::now();
        $startDate = $targetDate->copy()->subWeeks(12);
        
        // 1. Frecuencia Semanal (Días entrenados por semana)
        $weeklyFrequencyData = \App\Models\WorkoutLog::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $targetDate])
            ->get()
            ->groupBy(function($log) {
                return \Carbon\Carbon::parse($log->created_at)->startOfWeek()->format('M d');
            })
            ->map(function ($logs) {
                // Contar días únicos entrenados en esa semana
                return $logs->pluck('created_at')->map(fn($c) => $c->format('Y-m-d'))->unique()->count();
            });
            
        $weeklyFrequency = [];
        $currentWeek = $startDate->copy()->startOfWeek();
        for ($i = 0; $i < 12; $i++) {
            $weekKey = $currentWeek->format('M d');
            $weeklyFrequency[] = [
                'week' => $weekKey,
                'days' => $weeklyFrequencyData[$weekKey] ?? 0
            ];
            $currentWeek->addWeek();
        }

        // 2. Distribución por Categorías desde la Base de Datos
        $categories = \App\Models\WorkoutLog::where('workout_logs.user_id', $user->id)
            ->join('categories', 'workout_logs.category_id', '=', 'categories.id')
            ->select('categories.name as category_name', 'categories.icon', 'categories.color', \DB::raw('count(*) as count'))
            ->groupBy('categories.id', 'categories.name', 'categories.icon', 'categories.color')
            ->get();
        
        return [
            'weeklyFrequency' => $weeklyFrequency,
            'categories' => $categories
        ];
    }

    public function updateLookingForInterest(Request $request): \Illuminate\Http\JsonResponse
    {
        //Validamos los datos que nos llegan.
        $validated = $request->validate([
            'interest_id' => ['required', 'integer', Rule::exists('fitness_interests', 'id')],
        ]);

        //Obtenemos el usuario autenticado.
        $user = $request->user();

        //Actualizamos el campo específico.
        $user->looking_for_interest_id = $validated['interest_id'];

        //Guardamos los cambios en la base de datos.
        $user->save();

        //Devolvemos una respuesta JSON para confirmar que todo ha ido bien.
        return response()->json(['message' => 'Interest updated successfully.']);
    }


    public function updateBannerColor(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'banner_color' => ['required', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $user = $request->user();
        $user->banner_color = $validated['banner_color'];
        $user->save();

        return back()->with('success_toast', 'Color del banner actualizado correctamente.');
    }

    /**
     * Calcular racha de días consecutivos con entrenamientos
     */
    private function calculateStreak(User $user): int
    {
        $logs = $user->workoutLogs()
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function($log) {
                return $log->created_at->format('Y-m-d');
            });

        if ($logs->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $currentDate = now()->startOfDay();

        foreach ($logs->keys() as $date) {
            $logDate = \Carbon\Carbon::parse($date)->startOfDay();
            
            if ($logDate->equalTo($currentDate) || $logDate->equalTo($currentDate->copy()->subDay())) {
                $streak++;
                $currentDate = $logDate->copy()->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Calcular nivel del usuario basado en actividad
     */
    private function calculateLevel(User $user): int
    {
        $totalWorkouts = $user->workoutLogs()->count();
        $totalPosts = $user->posts()->count();
        $totalConnections = $user->sentConnections()->where('status', 'accepted')->count();

        // Fórmula simple: nivel = (entrenamientos + posts*2 + conexiones*3) / 10
        $points = ($totalWorkouts + ($totalPosts * 2) + ($totalConnections * 3));
        $level = max(1, floor($points / 10));

        return min($level, 100); // Máximo nivel 100
    }
}