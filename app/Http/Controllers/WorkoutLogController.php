<?php

namespace App\Http\Controllers;

use App\Models\WorkoutLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WorkoutLogController extends Controller
{
    /**
     * Display a listing of the user's workout logs
     */
    public function index(Request $request)
    {
        $logs = Auth::user()
            ->workoutLogs()
            ->with('workout:id,name')
            ->latest()
            ->paginate(20);

        return Inertia::render('Workouts/History', [
            'logs' => $logs,
            'title' => 'Historial de Entrenamientos',
        ]);
    }

    /**
     * Store a newly completed workout log
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'workout_id' => 'nullable|exists:workouts,id',
            'workout_name' => 'required|string|max:255',
            'exercises_data' => 'required|array',
            'duration_minutes' => 'nullable|integer|min:1',
            'total_sets' => 'required|integer|min:0',
            'completed_sets' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $log = WorkoutLog::create([
            'user_id' => Auth::id(),
            'workout_id' => $validated['workout_id'] ?? null,
            'workout_name' => $validated['workout_name'],
            'exercises_data' => $validated['exercises_data'],
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'total_sets' => $validated['total_sets'],
            'completed_sets' => $validated['completed_sets'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('workout-logs.show', $log)
            ->with('success_toast', '¡Entrenamiento registrado exitosamente!');
    }

    /**
     * Display the specified workout log
     */
    public function show(WorkoutLog $workoutLog)
    {
        // Verificar que el usuario sea el dueño
        if ($workoutLog->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        $workoutLog->load('workout:id,name');

        return Inertia::render('Workouts/LogDetail', [
            'log' => $workoutLog,
            'title' => 'Detalle del Entrenamiento',
        ]);
    }

    /**
     * Remove the specified workout log
     */
    public function destroy(WorkoutLog $workoutLog)
    {
        // Verificar que el usuario sea el dueño
        if ($workoutLog->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        $workoutLog->delete();

        return redirect()->route('workout-logs.index')
            ->with('success_toast', 'Registro eliminado exitosamente');
    }

    /**
     * Get personal records for the authenticated user
     */
    public function personalRecords()
    {
        $userId = Auth::id();
        $logs = WorkoutLog::where('user_id', $userId)->get();
        
        $records = [];
        
        foreach ($logs as $log) {
            foreach ($log->exercises_data as $exercise) {
                $exerciseName = $exercise['name'];
                
                if (!isset($records[$exerciseName])) {
                    $records[$exerciseName] = [
                        'name' => $exerciseName,
                        'max_weight' => 0,
                        'max_reps' => 0,
                        'max_volume' => 0,
                        'total_sessions' => 0,
                    ];
                }
                
                $records[$exerciseName]['total_sessions']++;
                
                foreach ($exercise['sets'] as $set) {
                    if ($set['completed'] ?? false) {
                        $weight = $set['weight'] ?? 0;
                        $reps = $set['reps'] ?? 0;
                        $volume = $weight * $reps;
                        
                        $records[$exerciseName]['max_weight'] = max($records[$exerciseName]['max_weight'], $weight);
                        $records[$exerciseName]['max_reps'] = max($records[$exerciseName]['max_reps'], $reps);
                        $records[$exerciseName]['max_volume'] = max($records[$exerciseName]['max_volume'], $volume);
                    }
                }
            }
        }
        
        return Inertia::render('Workouts/PersonalRecords', [
            'records' => array_values($records),
            'title' => 'Récords Personales',
        ]);
    }

    /**
     * Display calendar view of workout logs
     */
    public function calendar(Request $request)
    {
        $month = $request->get('month', date('n'));
        $year = $request->get('year', date('Y'));
        
        $startDate = date('Y-m-01', strtotime("$year-$month-01"));
        $endDate = date('Y-m-t', strtotime("$year-$month-01"));
        
        $logs = \App\Models\WorkoutLog::where('user_id', Auth::id())
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get(['id', 'workout_name', 'created_at', 'completed_sets', 'total_sets']);
        
        $monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        
        return Inertia::render('Workouts/Calendar', [
            'logs' => $logs,
            'currentMonth' => $monthNames[$month - 1],
            'currentYear' => (int)$year,
            'title' => 'Calendario de Entrenamientos',
        ]);
    }
}
