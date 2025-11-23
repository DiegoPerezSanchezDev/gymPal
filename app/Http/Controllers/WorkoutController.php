<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use App\Models\WorkoutExercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WorkoutController extends Controller
{
    /**
     * Display a listing of workouts (for explore/browse)
     */
    public function index(Request $request)
    {
        $query = Workout::query()
            ->with(['user:id,name,username,profile_picture_url', 'exercises'])
            ->public();

        // Filtros opcionales
        if ($request->category) {
            $query->where('category', $request->category);
        }
        
        if ($request->difficulty) {
            $query->byDifficulty($request->difficulty);
        }

        $workouts = $query->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Workouts/Index', [
            'workouts' => $workouts,
            'title' => 'Explorar Rutinas',
            'filters' => [
                'category' => $request->category,
                'difficulty' => $request->difficulty,
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
        ]);
    }

    /**
     * Store a newly created workout
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty_level' => 'required|in:principiante,intermedio,avanzado',
            'duration_minutes' => 'nullable|integer|min:1',
            'category' => 'required|string|max:100',
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

        $workout = Workout::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'difficulty_level' => $validated['difficulty_level'],
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'category' => $validated['category'],
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

        return redirect()->route('profile.show.public', ['user' => Auth::user()->username])
            ->with('success_toast', 'Rutina creada exitosamente');
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

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty_level' => 'required|in:principiante,intermedio,avanzado',
            'duration_minutes' => 'nullable|integer|min:1',
            'category' => 'required|string|max:100',
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

        $workout->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'difficulty_level' => $validated['difficulty_level'],
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'category' => $validated['category'],
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

        return redirect()->route('workouts.show', $workout)
            ->with('success_toast', 'Rutina actualizada exitosamente');
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
        }

        return response()->json([
            'is_saved' => !$isSaved,
            'times_saved' => $workout->times_saved,
            'message' => $message
        ]);
    }

    /**
     * Get user's saved workouts
     */
    public function saved()
    {
        $workouts = Auth::user()
            ->savedWorkouts()
            ->with(['user:id,name,username,profile_picture_url', 'exercises'])
            ->latest('saved_workouts.created_at')
            ->paginate(12);

        return Inertia::render('Workouts/Saved', [
            'workouts' => $workouts,
            'title' => 'Rutinas Guardadas',
        ]);
    }
    /**
     * Duplicate a workout (Fork/Clone)
     */
    public function duplicate(Workout $workout)
    {
        $newWorkout = $workout->replicate();
        $newWorkout->user_id = Auth::id();
        $newWorkout->name = $workout->name . ' (Copia)';
        $newWorkout->times_saved = 0; // Reset stats
        $newWorkout->created_at = now();
        $newWorkout->updated_at = now();
        $newWorkout->save();

        foreach ($workout->exercises as $exercise) {
            $newExercise = $exercise->replicate();
            $newExercise->workout_id = $newWorkout->id;
            $newExercise->save();
        }

        return redirect()->route('workouts.edit', $newWorkout)
            ->with('success_toast', 'Rutina duplicada exitosamente. Ahora puedes editarla.');
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

        return Inertia::render('Workouts/Live', [
            'workout' => $workout,
            'title' => 'Entrenar: ' . $workout->name,
        ]);
    }
}
