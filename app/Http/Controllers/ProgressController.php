<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Obtener todas las rutinas que el usuario ha completado al menos una vez
        $completedWorkouts = $user->workoutLogs()
            ->select('workout_name', 'workout_id', DB::raw('COUNT(*) as times_completed'))
            ->whereNotNull('workout_name')
            ->groupBy('workout_name', 'workout_id')
            ->orderBy('times_completed', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->workout_id,
                    'name' => $item->workout_name,
                    'times_completed' => $item->times_completed
                ];
            });

        // Si no hay rutinas completadas, mostrar estado vacío
        if ($completedWorkouts->isEmpty()) {
            return Inertia::render('Progress/Index', [
                'title' => 'Mi Progreso',
                'workouts' => [],
                'selectedWorkout' => null,
                'progressData' => null
            ]);
        }

        // Obtener la rutina seleccionada (por defecto la más completada)
        $selectedWorkoutName = $request->get('workout', $completedWorkouts->first()['name']);
        
        // Obtener todos los logs de esta rutina específica
        $logs = $user->workoutLogs()
            ->where('workout_name', $selectedWorkoutName)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($logs->isEmpty()) {
            return Inertia::render('Progress/Index', [
                'title' => 'Mi Progreso',
                'workouts' => $completedWorkouts,
                'selectedWorkout' => $selectedWorkoutName,
                'progressData' => null
            ]);
        }

        // Analizar progreso por ejercicio
        $exerciseProgress = [];
        
        foreach ($logs as $log) {
            if (!is_array($log->exercises_data)) continue;
            
            $sessionDate = Carbon::parse($log->created_at)->format('d M');
            
            foreach ($log->exercises_data as $exercise) {
                // Saltar si no tiene nombre (datos corruptos)
                if (!isset($exercise['name']) || empty($exercise['name'])) {
                    continue;
                }
                
                $exerciseName = $exercise['name'];
                
                if (!isset($exerciseProgress[$exerciseName])) {
                    $exerciseProgress[$exerciseName] = [
                        'name' => $exerciseName,
                        'sessions' => [],
                        'max_weight' => 0,
                        'total_volume' => 0
                    ];
                }
                
                // Calcular peso máximo y volumen de esta sesión
                $sessionMaxWeight = 0;
                $sessionVolume = 0;
                
                foreach ($exercise['sets'] as $set) {
                    if ($set['completed'] ?? false) {
                        $weight = (float)($set['weight'] ?? 0);
                        $reps = (int)($set['reps'] ?? 0);
                        
                        $sessionMaxWeight = max($sessionMaxWeight, $weight);
                        $sessionVolume += $weight * $reps;
                    }
                }
                
                $exerciseProgress[$exerciseName]['sessions'][] = [
                    'date' => $sessionDate,
                    'max_weight' => $sessionMaxWeight,
                    'volume' => $sessionVolume
                ];
                
                $exerciseProgress[$exerciseName]['max_weight'] = max(
                    $exerciseProgress[$exerciseName]['max_weight'],
                    $sessionMaxWeight
                );
                
                $exerciseProgress[$exerciseName]['total_volume'] += $sessionVolume;
            }
        }

        // Estadísticas generales de la rutina
        $stats = [
            'times_completed' => $logs->count(),
            'avg_duration' => round($logs->avg('duration_minutes')),
            'total_volume' => array_sum(array_column($exerciseProgress, 'total_volume')),
            'best_time' => $logs->min('duration_minutes'),
            'last_completed' => Carbon::parse($logs->last()->created_at)->diffForHumans()
        ];

        // Calcular récords personales (mejor set por ejercicio)
        $personalRecords = [];
        
        foreach ($logs as $log) {
            if (!is_array($log->exercises_data)) continue;
            
            foreach ($log->exercises_data as $exercise) {
                if (!isset($exercise['name']) || empty($exercise['name'])) continue;
                
                $exerciseName = $exercise['name'];
                
                if (!isset($personalRecords[$exerciseName])) {
                    $personalRecords[$exerciseName] = [
                        'name' => $exerciseName,
                        'best_weight' => 0,
                        'best_reps' => 0,
                        'best_volume' => 0, // peso × reps
                        'date' => null
                    ];
                }
                
                foreach ($exercise['sets'] as $set) {
                    if ($set['completed'] ?? false) {
                        $weight = (float)($set['weight'] ?? 0);
                        $reps = (int)($set['reps'] ?? 0);
                        $volume = $weight * $reps;
                        
                        // Actualizar si este set tiene mejor volumen (peso × reps)
                        if ($volume > $personalRecords[$exerciseName]['best_volume']) {
                            $personalRecords[$exerciseName]['best_weight'] = $weight;
                            $personalRecords[$exerciseName]['best_reps'] = $reps;
                            $personalRecords[$exerciseName]['best_volume'] = $volume;
                            $personalRecords[$exerciseName]['date'] = Carbon::parse($log->created_at)->format('d M Y');
                        }
                    }
                }
            }
        }
        
        // Ordenar por volumen descendente
        usort($personalRecords, function($a, $b) {
            return $b['best_volume'] <=> $a['best_volume'];
        });

        return Inertia::render('Progress/Index', [
            'title' => 'Mi Progreso',
            'workouts' => $completedWorkouts,
            'selectedWorkout' => $selectedWorkoutName,
            'progressData' => [
                'exercises' => array_values($exerciseProgress),
                'stats' => $stats,
                'personalRecords' => $personalRecords
            ]
        ]);
    }
}
