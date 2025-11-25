<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Entrenamientos por semana (últimas 8 semanas)
        $workoutsPerWeek = $user->workoutLogs()
            ->select(
                DB::raw('YEARWEEK(created_at, 1) as year_week'),
                DB::raw('COUNT(*) as count'),
                DB::raw('MIN(created_at) as week_start')
            )
            ->where('created_at', '>=', now()->subWeeks(8))
            ->groupBy('year_week')
            ->orderBy('year_week')
            ->get()
            ->map(function ($item) {
                return [
                    'week_label' => Carbon::parse($item->week_start)->format('d M'),
                    'count' => $item->count
                ];
            });

        // 2. Volumen total por semana (últimas 8 semanas) - Aproximado
        // Esto requeriría sumar sets * reps * weight de exercises_data (JSON).
        // Como es complejo hacerlo en SQL puro con JSON, lo haremos en colección si no son muchos datos,
        // o simplificamos mostrando "Minutos de entrenamiento" por semana.
        
        $minutesPerWeek = $user->workoutLogs()
            ->select(
                DB::raw('YEARWEEK(created_at, 1) as year_week'),
                DB::raw('SUM(duration_minutes) as total_minutes'),
                DB::raw('MIN(created_at) as week_start')
            )
            ->where('created_at', '>=', now()->subWeeks(8))
            ->groupBy('year_week')
            ->orderBy('year_week')
            ->get()
            ->map(function ($item) {
                return [
                    'week_label' => Carbon::parse($item->week_start)->format('d M'),
                    'minutes' => (int)$item->total_minutes
                ];
            });

        // 3. Récords Personales (Top 5 ejercicios más frecuentes)
        // Reutilizamos lógica de WorkoutLogController pero simplificada
        $logs = $user->workoutLogs()->latest()->take(50)->get(); // Últimos 50 logs para análisis rápido
        $records = [];

        foreach ($logs as $log) {
            if (!is_array($log->exercises_data)) continue;
            
            foreach ($log->exercises_data as $exercise) {
                $name = $exercise['name'] ?? 'Ejercicio';
                if (!isset($records[$name])) {
                    $records[$name] = 0;
                }
                // Buscamos el peso máximo en este log
                foreach ($exercise['sets'] as $set) {
                    if (($set['completed'] ?? false) && isset($set['weight'])) {
                        $records[$name] = max($records[$name], (float)$set['weight']);
                    }
                }
            }
        }
        
        // Ordenar por peso (solo como ejemplo, idealmente sería por relevancia)
        arsort($records);
        $topRecords = array_slice($records, 0, 5);

        return Inertia::render('Progress/Index', [
            'title' => 'Mi Progreso',
            'workoutsPerWeek' => $workoutsPerWeek,
            'minutesPerWeek' => $minutesPerWeek,
            'personalRecords' => $topRecords
        ]);
    }
}
