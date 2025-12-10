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
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'title' => 'Editar Perfil',
            'isLoginPage' => false, // Estas props pueden ser útiles para el layout
            'isRegisterPage' => false,
            'user' => $request->user()->load('fitnessInterests'), 
            'interests' => \App\Models\FitnessInterest::all(['id', 'name']),
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
        

        $user->load('fitnessInterests');

        $user->loadCount('posts');
        
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
                ->distinct('created_at')
                ->count(\DB::raw('DATE(created_at)')),
            'streak_days' => $this->calculateStreak($user),
            'workouts_created' => $user->workouts()->count(),
            'posts_count' => $user->posts()->count(),
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

        return Inertia::render('Profile/ShowPublic', [
            'profileUser' => $user,
            'title' => 'Perfil de ' . $user->name,
            'isOwnProfile' => $currentUser ? $currentUser->id === $user->id : false,
            
            'connection_status' => $connectionStatus,
            'connection_id' => $connection ? $connection->id : null,
            'is_following_me' => $isFollowingMe, // Indica si el perfil visitado me sigue
            
            // Contadores y listas completas
            'gym_pals_count' => $gymPalsCount,
            'followers_count' => $followersCount,
            'following_count' => $followingCount,
            'gym_pals_list' => $gymPals,
            'followers_list' => $followersAccepted->values(), // Lista completa de seguidores
            'following_list' => $followingAccepted->values(), // Lista completa de siguiendo
            
            // Estadísticas del usuario
            'stats' => $stats,
            
            'posts' => $posts, // Pasar los posts
            'workouts' => $workouts, // Pasar las rutinas
        ]);
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