<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Models\Category;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class WorkoutController extends Controller
{
    /**
     * Display a listing of workouts (for explore/browse)
     */
    public function index(Request $request)
    {
        $query = Workout::query()
            ->with(['user:id,name,username,profile_picture_url', 'exercises', 'category'])
            ->public();

        // Excluir rutinas propias y limitar a seguidos
        if (Auth::check()) {
            $user = Auth::user();
            $gymPalIds = $user->gym_pals->pluck('id');
            
            $query->whereIn('user_id', $gymPalIds);
            // No hace falta excluir el propio ID si no está en gym_pals, pero por seguridad:
            $query->where('user_id', '!=', $user->id);
        }

        // Filtros opcionales
        if ($request->category) {
            $query->where(function($q) use ($request) {
                $q->where('category', $request->category)
                  ->orWhereHas('category', function($q2) use ($request) {
                      $q2->where('slug', $request->category);
                      if (is_numeric($request->category)) {
                          $q2->orWhere('id', (int)$request->category);
                      }
                  });
            });
        }
        
        if ($request->difficulty) {
            $query->byDifficulty($request->difficulty);
        }

        // Búsqueda general (nombre de rutina o usuario)
        if ($request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhereHas('user', function($q2) use ($searchTerm) {
                      $q2->where('name', 'like', "%{$searchTerm}%")
                         ->orWhere('username', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Sistema de ordenamiento
        $sortBy = $request->get('sort', 'popular'); // Por defecto: populares
        
        switch ($sortBy) {
            case 'recent':
                $query->latest();
                break;
            case 'exercises':
                // Ordenar por número de ejercicios (más completas)
                $query->withCount('exercises')->orderBy('exercises_count', 'desc');
                break;
            case 'popular':
            default:
                // Ordenar por popularidad (más guardadas)
                $query->orderBy('times_saved', 'desc')->latest();
                break;
        }

        $workouts = $query->paginate(12)
            ->withQueryString();

        return Inertia::render('Workouts/Index', [
            'workouts' => $workouts,
            'categories' => Category::all(),
            'title' => 'Explorar Rutinas',
            'filters' => [
                'category' => $request->category,
                'difficulty' => $request->difficulty,
                'search' => $request->search,
                'sort' => $sortBy,
            ]
        ]);
    }

    /**
     * Show the form for creating a new workout
     */
    public function create()
    {
        return Inertia::render('Workouts/Create', [
            'title' => 'Crear Rutina',
            'categories' => Category::all(),
        ]);
    }

    /**
     * Store a newly created workout
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'difficulty' => 'required|in:Principiante,Intermedio,Avanzado',
                'duration_minutes' => 'nullable|integer|min:1',
                'category_id' => 'required|exists:categories,id',
                'is_public' => 'boolean',
                'exercises' => 'required|array|min:1',
                'exercises.*.exercise_name' => 'required|string|max:255',
                'exercises.*.sets_data' => 'required|array|min:1',
                'exercises.*.sets_data.*.reps' => 'required|integer|min:1',
                'exercises.*.sets_data.*.weight' => 'nullable|numeric|min:0',
                'exercises.*.sets_data.*.type' => 'nullable|string',
                'exercises.*.rest_seconds' => 'nullable|integer|min:0',
                'exercises.*.notes' => 'nullable|string',
            ]);

            $category = Category::find($validated['category_id']);

            $workout = Workout::create([
                'user_id' => Auth::id(),
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'difficulty' => $validated['difficulty'],
                'duration_minutes' => $validated['duration_minutes'] ?? null,
                'category_id' => $validated['category_id'],
                'category' => $category->name, // Keep for backward compatibility
                'is_public' => $validated['is_public'] ?? true,
            ]);

            // Crear ejercicios
            foreach ($validated['exercises'] as $index => $exercise) {
                WorkoutExercise::create([
                    'workout_id' => $workout->id,
                    'exercise_name' => $exercise['exercise_name'],
                    'sets_data' => $exercise['sets_data'],
                    'rest_seconds' => $exercise['rest_seconds'] ?? 60,
                    'notes' => $exercise['notes'] ?? null,
                    'order' => $index,
                ]);
            }

            // Detectar de dónde viene el usuario para redirigir apropiadamente
            $referer = $request->headers->get('referer');
            $redirectToMyWorkouts = $referer && str_contains($referer, 'my-workouts');

            if ($redirectToMyWorkouts) {
                return redirect()->route('workouts.my-workouts')
                    ->with('success_toast', 'Rutina creada exitosamente');
            }

            return redirect()->route('profile.show.public', ['user' => Auth::user()->username])
                ->with('success_toast', 'Rutina creada exitosamente');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error creating workout: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al guardar la rutina: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified workout
     */
    public function show(Workout $workout)
    {
        $workout->load([
            'user:id,name,username,profile_picture_url',
            'exercises' => function($query) {
                $query->orderBy('order');
            }
        ]);

        $isSaved = Auth::check() ? Auth::user()->savedWorkouts()->where('workout_id', $workout->id)->exists() : false;
        $isOwner = Auth::check() && Auth::id() === $workout->user_id;
        
        // Obtener logs de esta rutina para el usuario autenticado
        $logsCount = 0;
        $recentLogs = [];
        if (Auth::check()) {
            $logsCount = \App\Models\WorkoutLog::where('user_id', Auth::id())
                ->where('workout_id', $workout->id)
                ->count();
            
            if ($logsCount > 0) {
                $recentLogs = \App\Models\WorkoutLog::where('user_id', Auth::id())
                    ->where('workout_id', $workout->id)
                    ->latest()
                    ->take(3)
                    ->get(['id', 'created_at', 'duration_minutes', 'completed_sets', 'total_sets']);
            }
        }

        return Inertia::render('Workouts/Show', [
            'workout' => $workout,
            'isSaved' => $isSaved,
            'isOwner' => $isOwner,
            'logsCount' => $logsCount,
            'recentLogs' => $recentLogs,
            'title' => $workout->name,
        ]);
    }

    /**
     * Show the form for editing the specified workout
     */
    public function edit(Workout $workout)
    {
        // Verificar que el usuario sea el dueño
        if ($workout->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        $workout->load('exercises');

        return Inertia::render('Workouts/Edit', [
            'workout' => $workout,
            'categories' => Category::all(),
            'title' => 'Editar Rutina',
        ]);
    }

    /**
     * Update the specified workout
     */
    public function update(Request $request, Workout $workout)
    {
        // Verificar que el usuario sea el dueño
        if ($workout->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'difficulty' => 'required|in:Principiante,Intermedio,Avanzado',
                'duration_minutes' => 'nullable|integer|min:1',
                'category_id' => 'required|exists:categories,id',
                'is_public' => 'boolean',
                'exercises' => 'required|array|min:1',
                'exercises.*.exercise_name' => 'required|string|max:255',
                'exercises.*.sets_data' => 'required|array|min:1',
                'exercises.*.sets_data.*.reps' => 'required|integer|min:1',
                'exercises.*.sets_data.*.weight' => 'nullable|numeric|min:0',
                'exercises.*.sets_data.*.type' => 'nullable|string',
                'exercises.*.rest_seconds' => 'nullable|integer|min:0',
                'exercises.*.notes' => 'nullable|string',
            ]);

            $category = Category::find($validated['category_id']);

            $workout->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'difficulty' => $validated['difficulty'],
                'duration_minutes' => $validated['duration_minutes'] ?? null,
                'category_id' => $validated['category_id'],
                'category' => $category->name, // Keep for backward compatibility
                'is_public' => $validated['is_public'] ?? true,
            ]);

            // Eliminar ejercicios antiguos y recrear
            $workout->exercises()->delete();

            foreach ($validated['exercises'] as $index => $exercise) {
                WorkoutExercise::create([
                    'workout_id' => $workout->id,
                    'exercise_name' => $exercise['exercise_name'],
                    'sets_data' => $exercise['sets_data'],
                    'rest_seconds' => $exercise['rest_seconds'] ?? 60,
                    'notes' => $exercise['notes'] ?? null,
                    'order' => $index,
                ]);
            }

            // Detectar de dónde viene el usuario para redirigir apropiadamente
            $referer = $request->headers->get('referer');
            $redirectToMyWorkouts = $referer && str_contains($referer, 'my-workouts');

            if ($redirectToMyWorkouts) {
                return redirect()->route('workouts.my-workouts')
                    ->with('success_toast', 'Rutina actualizada exitosamente');
            }

            return redirect()->route('workouts.show', $workout)
                ->with('success_toast', 'Rutina actualizada exitosamente');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating workout: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar la rutina: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified workout
     */
    public function destroy(Workout $workout)
    {
        // Verificar que el usuario sea el dueño
        if ($workout->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        $workout->delete();

        return redirect()->route('profile.show.public', ['user' => Auth::user()->username])
            ->with('success_toast', 'Rutina eliminada exitosamente');
    }

    /**
     * Toggle save workout (like Pinterest)
     */
    public function toggleSave(Workout $workout)
    {
        try {
            $user = Auth::user();
            $isSaved = $user->savedWorkouts()->where('workout_id', $workout->id)->exists();

            if ($isSaved) {
                $user->savedWorkouts()->detach($workout->id);
                $workout->decrementSaveCount();
                $message = 'Rutina eliminada de guardados';
            } else {
                $user->savedWorkouts()->attach($workout->id);
                $workout->incrementSaveCount();
                $message = 'Rutina guardada exitosamente';
                
                // Notificar al dueño si no es el mismo usuario
            if ($workout->user_id !== $user->id) {
                try {
                    NotificationService::create(
                        $workout->user,
                        'workout_saved',
                        $user->name . ' guardó tu rutina',
                        'guardó tu rutina',
                        $workout,
                        [
                            'user_id' => $user->id,
                            'user_name' => $user->name,
                            'user_avatar' => $user->profile_picture_url,
                            'workout_id' => $workout->id,
                            'workout_name' => $workout->name,
                        ]
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error sending workout notification: ' . $e->getMessage());
                    // No fallamos la request si solo falla la notificación
                }
            }          }
            

            if (request()->wantsJson()) {
                return response()->json([
                    'is_saved' => !$isSaved,
                    'times_saved' => $workout->times_saved,
                    'message' => $message
                ]);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in toggleSave: ' . $e->getMessage());
            if (request()->wantsJson()) {
                return response()->json(['error' => 'Error interno al guardar rutina'], 500);
            }
            return back()->withErrors(['error' => 'Error al guardar rutina']);
        }
    }

    /**
     * Get user's saved workouts
     */
    public function saved()
    {
        $user = Auth::user();
        
        $workouts = $user
            ->savedWorkouts()
            ->with(['user:id,name,username,profile_picture_url', 'exercises'])
            ->latest('saved_workouts.created_at')
            ->paginate(12);

        // Obtener posts guardados
        $savedPosts = $user->savedPosts()
            ->with(['user', 'latestLikers', 'latestComments.user', 'workout', 'workoutLog'])
            ->withCount(['likers', 'comments'])
            ->latest('saved_posts.created_at')
            ->paginate(10);
            
        // Añadir is_liked e is_saved a cada post
        $savedPosts->getCollection()->transform(function ($post) use ($user) {
            $post->is_liked = $post->likers()->where('user_id', $user->id)->exists();
            $post->is_saved = true;
            return $post;
        });

        return Inertia::render('Workouts/Saved', [
            'workouts' => $workouts,
            'savedPosts' => $savedPosts,
            'title' => 'Guardados',
        ]);
    }
    /**
     * Duplicate a workout (Fork/Clone)
     */
    public function duplicate(Request $request, Workout $workout)
    {
        $newWorkout = $workout->replicate();
        $newWorkout->user_id = Auth::id();
        $newWorkout->name = $workout->name . ' (Copia)';
        $newWorkout->is_public = $request->input('is_public', false); // Default: privado
        $newWorkout->original_workout_id = $workout->id; // Asumiendo que existe esta columna o similar para tracking
        $newWorkout->save();

        // Copiar ejercicios
        foreach ($workout->exercises as $exercise) {
            $newExercise = $exercise->replicate();
            $newExercise->workout_id = $newWorkout->id;
            $newExercise->save();
        }

        // Notificar al dueño original si no es el mismo usuario
    if ($workout->user_id !== Auth::id()) {
        NotificationService::create(
            $workout->user,
            'workout_cloned',
            Auth::user()->name . ' clonó tu rutina',
            'clonó tu rutina',
            $workout,
            [
                'user_id' => Auth::user()->id,
                'user_name' => Auth::user()->name,
                'user_avatar' => Auth::user()->profile_picture_url,
                'workout_id' => $workout->id,
                'workout_name' => $workout->name,
            ]
        );
    }
        return redirect()->route('workouts.edit', $newWorkout->id)
            ->with('success', 'Rutina clonada exitosamente. Ahora puedes editarla.');
    }

    /**
     * Start live workout mode
     */
    public function live(Workout $workout)
    {
        $workout->load([
            'user:id,name,username,profile_picture_url',
            'exercises' => function($query) {
                $query->orderBy('order');
            }
        ]);

        // Obtener PRs para los ejercicios de esta rutina
        $exerciseNames = $workout->exercises->pluck('exercise_name')->map(fn($name) => strtolower($name))->unique();
        
        $logs = \App\Models\WorkoutLog::where('user_id', Auth::id())->get();
        $personalRecords = [];

        foreach ($exerciseNames as $name) {
            $personalRecords[$name] = [
                'max_weight' => 0,
                'max_reps' => 0,
                'max_volume' => 0
            ];
        }

        foreach ($logs as $log) {
            foreach ($log->exercises_data as $exercise) {
                if (!isset($exercise['name'])) continue;
                $name = strtolower($exercise['name']);
                if (in_array($name, $exerciseNames->toArray())) {
                    foreach ($exercise['sets'] as $set) {
                        if ($set['completed'] ?? false) {
                            $weight = $set['weight'] ?? 0;
                            $reps = $set['reps'] ?? 0;
                            
                            // Lógica de "Mejor Set": Mayor peso gana. Si empate, mayor reps gana.
                            $currentBest = $personalRecords[$name];
                            
                            if ($weight > $currentBest['max_weight']) {
                                // Nuevo peso máximo encontrado
                                $personalRecords[$name] = [
                                    'max_weight' => $weight,
                                    'max_reps' => $reps, // Guardamos las reps de ESTE peso
                                    'max_volume' => $weight * $reps
                                ];
                            } elseif ($weight == $currentBest['max_weight'] && $reps > $currentBest['max_reps']) {
                                // Mismo peso pero más reps
                                $personalRecords[$name]['max_reps'] = $reps;
                                $personalRecords[$name]['max_volume'] = $weight * $reps;
                            }
                        }
                    }
                }
            }
        }

        return Inertia::render('Workouts/Live', [
            'workout' => $workout,
            'personalRecords' => $personalRecords,
            'title' => 'Entrenar: ' . $workout->name,
        ]);
    }

    /**
     * Display user's own workouts with visibility filter
     */
    public function myWorkouts(Request $request)
    {
        $query = Workout::query()
            ->with(['exercises', 'category'])
            ->where('user_id', Auth::id());

        // Filtro de visibilidad
        if ($request->has('visibility')) {
            if ($request->visibility === 'public') {
                $query->where('is_public', true);
            } elseif ($request->visibility === 'private') {
                $query->where('is_public', false);
            }
            // Si es 'all' o no está definido, no filtramos
        }

        // Filtro de categoría (por ID, Slug o Nombre)
        if ($request->category) {
            $query->where(function($q) use ($request) {
                $q->where('category', $request->category)
                  ->orWhereHas('category', function($q2) use ($request) {
                      $q2->where('slug', $request->category);
                      if (is_numeric($request->category)) {
                          $q2->orWhere('id', (int)$request->category);
                      }
                  });
            });
        }

        // Filtro de dificultad
        if ($request->difficulty) {
            $query->byDifficulty($request->difficulty);
        }

        $workouts = $query->latest()
            ->paginate(12)
            ->withQueryString();

        // Contar totales
        $totalPublic = Workout::where('user_id', Auth::id())->where('is_public', true)->count();
        $totalPrivate = Workout::where('user_id', Auth::id())->where('is_public', false)->count();

        return Inertia::render('Workouts/MyWorkouts', [
            'workouts' => $workouts,
            'categories' => Category::all(),
            'totalPublic' => $totalPublic,
            'totalPrivate' => $totalPrivate,
            'title' => 'Mis Rutinas',
            'filters' => [
                'visibility' => $request->visibility ?? 'all',
                'category' => $request->category,
                'difficulty' => $request->difficulty,
            ]
        ]);
    }

    /**
     * Comparte una rutina con otro usuario enviándola como mensaje
     */
    public function shareWorkout(Request $request, Workout $workout)
    {
        try {
            $validated = $request->validate([
                'recipient_id' => 'required|exists:users,id',
            ]);

            $currentUser = Auth::user();
            $recipient = \App\Models\User::findOrFail($validated['recipient_id']);
            
            // Cargar la relación del usuario de la rutina
            $workout->load(['user', 'exercises']);

            // Verificar que el usuario esté conectado con el destinatario
            $isConnected = $currentUser->gym_pals->contains('id', $recipient->id);
            
            if (!$isConnected) {
                return response()->json(['message' => 'Solo puedes compartir con tus GymPals.'], 403);
            }

            // Buscar o crear conversación entre los dos usuarios
            $conversations = \App\Models\Conversation::whereHas('users', function ($q) use ($currentUser) {
                    $q->where('users.id', $currentUser->id);
                })
                ->whereHas('users', function ($q) use ($recipient) {
                    $q->where('users.id', $recipient->id);
                })
                ->get();

            // Filtrar para encontrar la conversación que tenga exactamente 2 usuarios
            $conversation = $conversations->first(function ($conv) {
                return $conv->users()->count() === 2;
            });

            if (!$conversation) {
                $conversation = \App\Models\Conversation::create(['last_message_at' => now()]);
                $conversation->users()->attach([$currentUser->id, $recipient->id]);
            }

            // Crear mensaje con la rutina compartida
            $workoutUrl = route('workouts.show', $workout->id);
            $messageBody = "🏋️ Compartí una rutina contigo";

            $metadata = [
                'workout_id' => $workout->id,
                'workout_url' => $workoutUrl,
                'workout_name' => $workout->name,
                'workout_description' => $workout->description,
                'workout_category' => $workout->category,
                'workout_difficulty' => $workout->difficulty,
                'workout_duration_minutes' => $workout->duration_minutes,
                'workout_exercises_count' => $workout->exercises->count(),
                'workout_author_name' => $workout->user->name,
                'workout_author_username' => $workout->user->username,
                'workout_author_profile_picture' => $workout->user->profile_picture_url,
            ];

            $message = $conversation->messages()->create([
                'user_id' => $currentUser->id,
                'body' => $messageBody,
                'type' => \App\Models\Message::TYPE_SHARED_WORKOUT,
                'metadata' => $metadata,
            ]);

            $conversation->update(['last_message_at' => now()]);

            // Crear notificación para el destinatario
            NotificationService::create(
                $recipient,
                'workout_shared',
                $currentUser->name . ' compartió una rutina contigo',
                'compartió una rutina contigo',
                $workout,
                [
                    'user_id' => $currentUser->id,
                    'user_name' => $currentUser->name,
                    'user_avatar' => $currentUser->profile_picture_url,
                    'workout_id' => $workout->id,
                    'workout_name' => $workout->name,
                ]
            );

            return response()->json([
                'message' => 'Rutina compartida exitosamente.',
                'conversation_id' => $conversation->id,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error sharing workout: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['message' => 'Error interno al compartir la rutina: ' . $e->getMessage()], 500);
        }
    }
}
