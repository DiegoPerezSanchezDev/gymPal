<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use App\Models\WorkoutLog;
use App\Models\User;
use App\Models\Connection;
use App\Models\Goal;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StatsController extends Controller
{
    // Ver tus propias estadísticas -> Redirige a show con tu propio ID
    public function index(Request $request)
    {
        return $this->show($request, auth()->user());
    }
    
    // Ver estadísticas (propias o de otro usuario)
    public function show(Request $request, User $user)
    {
        $currentUser = Auth::user();
        
        // 1. Verificar si son GymPals o es el propio perfil
        $isOwnProfile = $currentUser->id === $user->id;
        
        $areGymPals = false;
        if (!$isOwnProfile) {
            // Verificar si existe una conexión aceptada en cualquier dirección
            $areGymPals = Connection::where(function($query) use ($currentUser, $user) {
                $query->where('sender_id', $currentUser->id)
                      ->where('receiver_id', $user->id);
            })->orWhere(function($query) use ($currentUser, $user) {
                $query->where('sender_id', $user->id)
                      ->where('receiver_id', $currentUser->id);
            })->where('status', 'accepted')->exists();
        }

        if (!$isOwnProfile && !$areGymPals) {
            abort(403, 'No tienes permiso para ver las estadísticas de este usuario.');
        }

        // Obtener año seleccionado o actual
        $selectedYear = $request->input('year', Carbon::now()->year);
        
        // Obtener años disponibles con entrenamientos
        $availableYears = WorkoutLog::where('user_id', $user->id)
            ->selectRaw('EXTRACT(YEAR FROM created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->map(fn($year) => (int)$year)
            ->toArray();
            
        if (empty($availableYears)) {
            $availableYears = [Carbon::now()->year];
        } 
        
        // Asegurar que el año actual siempre está disponible
        if (!in_array(Carbon::now()->year, $availableYears)) {
            array_unshift($availableYears, Carbon::now()->year);
        }

        // Calcular datos
        $stats = $this->calculateStats($user);
        $levelData = $this->calculateLevel($user);
        
        // Datos filtrados por año
        $heatmapData = $this->getHeatmapData($user, $selectedYear);
        $progressCharts = $this->getProgressCharts($user, $selectedYear);
        
        // Datos generales (sin filtro de año)
        $streak = $this->calculateStreak($user);
        $personalRecords = $this->getPersonalRecords($user);
        $badges = $this->getBadges($user, $stats);
        $insights = $this->generateInsights($user, $stats, $streak);
        
        // Si estamos viendo a otro, calcular MIS estadísticas para comparar
        $myStats = null;
        if (!$isOwnProfile) {
            $myBasicStats = $this->calculateStats($currentUser);
            $myLevelData = $this->calculateLevel($currentUser);
            $myStreak = $this->calculateStreak($currentUser);
            
            $myStats = [
                'stats' => $myBasicStats,
                'level' => $myLevelData,
                'streak' => $myStreak
            ];
        }
        
        return Inertia::render('Stats/Index', [
            'stats' => $stats,
            'level' => $levelData,
            'heatmap' => $heatmapData,
            'streak' => $streak,
            'personalRecords' => $personalRecords,
            'badges' => $badges,
            'progressCharts' => $progressCharts,
            'insights' => $insights,
            'goals' => $this->getGoals($user), // <--- NUEVO
            'leaderboard' => $this->getLeaderboard($user),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'avatar' => $user->profile_picture_url,
            ],
            'isViewingOther' => !$isOwnProfile,
            'comparison' => $myStats,
            'filters' => [
                'year' => (int)$selectedYear,
                'availableYears' => $availableYears
            ]
        ]);
    }

    private function getLeaderboard($user)
    {
        // 1. Obtener IDs de todos los GymPals (conexiones mutuas)
        $sentIds = Connection::where('sender_id', $user->id)->pluck('receiver_id');
        $receivedIds = Connection::where('receiver_id', $user->id)->pluck('sender_id');
        
        // Unir todos los IDs y añadir al propio usuario
        $friendIds = $sentIds->merge($receivedIds)->push($user->id)->unique();
        
        // 2. Contar entrenamientos de esta semana para todos
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        $ranking = User::whereIn('id', $friendIds)
            ->withCount(['workoutLogs as weekly_workouts' => function ($query) use ($startOfWeek, $endOfWeek) {
                $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
            }])
            ->get()
            ->map(function ($friend) use ($user) {
                return [
                    'id' => $friend->id,
                    'name' => $friend->name,
                    'username' => $friend->username,
                    'avatar' => $friend->profile_picture_url,
                    'workouts' => $friend->weekly_workouts,
                    'is_me' => $friend->id === $user->id
                ];
            })
            ->sortByDesc('workouts')
            ->values()
            ->take(10);
            
        return $ranking;
    }
    
    private function calculateStats($user)
    {
        $totalLogs = WorkoutLog::where('user_id', $user->id)->count();
        $workoutLogs = WorkoutLog::where('user_id', $user->id)->get();
        $totalVolume = 0;
        $totalExercises = 0;
        
        foreach ($workoutLogs as $log) {
            if ($log->exercises_data && is_array($log->exercises_data)) {
                foreach ($log->exercises_data as $exercise) {
                    $totalExercises++;
                    if (isset($exercise['sets']) && is_array($exercise['sets'])) {
                        foreach ($exercise['sets'] as $set) {
                            if (isset($set['completed']) && $set['completed']) {
                                $weight = $set['weight'] ?? 0;
                                $reps = $set['reps'] ?? 0;
                                $totalVolume += $weight * $reps;
                            }
                        }
                    }
                }
            }
        }
        
        $totalMinutes = $totalLogs * 45;
        
        $workoutsThisMonth = WorkoutLog::where('user_id', $user->id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        
        $workoutsThisWeek = WorkoutLog::where('user_id', $user->id)
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])
            ->count();
        
        // Check last 3 months average
        $threeMonthsAgo = Carbon::now()->subMonths(3);
        $totalInPeriod = WorkoutLog::where('user_id', $user->id)
            ->where('created_at', '>=', $threeMonthsAgo)
            ->count();
            
        $avgWeekly = $totalInPeriod / 12; // Aprox 12 weeks in 3 months
        
        return [
            'totalWorkouts' => $totalLogs,
            'totalExercises' => $totalExercises,
            'totalVolume' => round($totalVolume),
            'totalMinutes' => $totalMinutes,
            'totalHours' => round($totalMinutes / 60, 1),
            'workoutsThisMonth' => $workoutsThisMonth,
            'workoutsThisWeek' => $workoutsThisWeek,
            'avgWeekly' => round($avgWeekly, 1),
        ];
    }
    
    private function calculateLevel($user)
    {
        $totalWorkouts = WorkoutLog::where('user_id', $user->id)->count();
        $xp = $totalWorkouts * 100;
        $level = floor(sqrt($xp / 100));
        if ($level < 1) $level = 1;
        
        $nextLevelXP = pow($level + 1, 2) * 100;
        $currentLevelXP = pow($level, 2) * 100;
        
        $progressXP = $xp - $currentLevelXP;
        $requiredXP = $nextLevelXP - $currentLevelXP;
        $progressPercent = $requiredXP > 0 ? ($progressXP / $requiredXP) * 100 : 100;
        
        // Calcular Rango Épico
        $rankName = 'Novato';
        if ($level >= 50) $rankName = 'Dios del Olimpo ⚡';
        else if ($level >= 30) $rankName = 'Titán 🛡️';
        else if ($level >= 20) $rankName = 'Espartano ⚔️';
        else if ($level >= 10) $rankName = 'Guerrero 🏹';
        else if ($level >= 5) $rankName = 'Calistenico 🤸';

        return [
            'level' => $level,
            'rankName' => $rankName,
            'xp' => $xp,
            'nextLevelXP' => $nextLevelXP,
            'currentLevelXP' => $currentLevelXP,
            'progressXP' => $progressXP,
            'requiredXP' => $requiredXP,
            'progressPercent' => round($progressPercent, 1),
        ];
    }
    
    private function getHeatmapData($user, $year)
    {
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();
        
        $workoutLogs = WorkoutLog::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('created_at::date as workout_date'), DB::raw('COUNT(*) as count'))
            ->groupBy('workout_date')
            ->get()
            ->keyBy('workout_date');
        
        $heatmap = [];
        $startDayOfWeek = $startDate->dayOfWeekIso;
        if ($startDayOfWeek > 1) {
            for ($i = 1; $i < $startDayOfWeek; $i++) {
                $heatmap[] = [
                    'date' => null,
                    'count' => 0,
                    'level' => -1,
                    'dummy' => true
                ];
            }
        }
        
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $count = $workoutLogs->has($dateStr) ? $workoutLogs[$dateStr]->count : 0;
            
            $heatmap[] = [
                'date' => $dateStr,
                'count' => $count,
                'level' => $this->getHeatmapLevel($count),
            ];
            $currentDate->addDay();
        }
        return $heatmap;
    }
    
    private function getHeatmapLevel($count)
    {
        if ($count === 0) return 0;
        if ($count === 1) return 1;
        if ($count === 2) return 2;
        if ($count === 3) return 3;
        return 4;
    }
    
    private function calculateStreak($user)
    {
        $workoutLogs = WorkoutLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->pluck('created_at')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
            ->unique()
            ->values();
        
        if ($workoutLogs->isEmpty()) {
            return [
                'current' => 0,
                'longest' => 0,
                'lastWorkout' => null,
            ];
        }
        
        $currentStreak = 0;
        $longestStreak = 0;
        $tempStreak = 1;
        
        $today = Carbon::now()->format('Y-m-d');
        $yesterday = Carbon::now()->subDay()->format('Y-m-d');
        
        if ($workoutLogs[0] === $today || $workoutLogs[0] === $yesterday) {
            $currentStreak = 1;
            $lastDate = Carbon::parse($workoutLogs[0]);
            for ($i = 1; $i < count($workoutLogs); $i++) {
                $currentDate = Carbon::parse($workoutLogs[$i]);
                $diff = $lastDate->diffInDays($currentDate);
                if ($diff === 1) {
                    $currentStreak++;
                    $lastDate = $currentDate;
                } else {
                    break;
                }
            }
        }
        
        for ($i = 0; $i < count($workoutLogs) - 1; $i++) {
            $currentDate = Carbon::parse($workoutLogs[$i]);
            $nextDate = Carbon::parse($workoutLogs[$i + 1]);
            $diff = $currentDate->diffInDays($nextDate);
            if ($diff === 1) {
                $tempStreak++;
            } else {
                $longestStreak = max($longestStreak, $tempStreak);
                $tempStreak = 1;
            }
        }
        $longestStreak = max($longestStreak, $tempStreak, $currentStreak ?: ($workoutLogs->isEmpty() ? 0 : 1));
        
        return [
            'current' => $currentStreak,
            'longest' => $longestStreak,
            'lastWorkout' => $workoutLogs[0] ?? null,
        ];
    }
    
    private function getPersonalRecords($user)
    {
        $workoutLogs = WorkoutLog::where('user_id', $user->id)->get();
        $exerciseStats = [];
        
        foreach ($workoutLogs as $log) {
            if ($log->exercises_data && is_array($log->exercises_data)) {
                foreach ($log->exercises_data as $exercise) {
                    $exerciseName = $exercise['name'] ?? 'Unknown';
                    if (!isset($exerciseStats[$exerciseName])) {
                        $exerciseStats[$exerciseName] = [
                            'frequency' => 0,
                            'maxWeight' => 0,
                            'maxWeightReps' => 0,
                        ];
                    }
                    $exerciseStats[$exerciseName]['frequency']++;

                    if (isset($exercise['sets']) && is_array($exercise['sets'])) {
                        foreach ($exercise['sets'] as $set) {
                            if (isset($set['completed']) && $set['completed']) {
                                $weight = $set['weight'] ?? 0;
                                $reps = $set['reps'] ?? 0;
                                if ($weight > $exerciseStats[$exerciseName]['maxWeight']) {
                                    $exerciseStats[$exerciseName]['maxWeight'] = $weight;
                                    $exerciseStats[$exerciseName]['maxWeightReps'] = $reps;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        uasort($exerciseStats, function($a, $b) {
            return $b['frequency'] - $a['frequency'];
        });
        
        $records = [];
        $count = 0;
        foreach ($exerciseStats as $exerciseName => $stats) {
            if ($count >= 5) break;
            $records[] = [
                'exercise' => $exerciseName,
                'maxWeight' => $stats['maxWeight'],
                'maxWeightReps' => $stats['maxWeightReps'],
                'frequency' => $stats['frequency'],
            ];
            $count++;
        }
        return $records;
    }
    
    private function getBadges($user, $stats)
    {
        $streak = $this->calculateStreak($user);
        
        $logs = WorkoutLog::where('user_id', $user->id)->get();
        $morningWorkouts = 0;
        $nightWorkouts = 0;
        $weekendWorkouts = 0;
        $maxWeightLifted = 0;
        $uniqueExercises = [];
        $maxDuration = 0;
        
        // Agrupar por semanas para calcular max workouts/week
        $workoutsByWeek = $logs->groupBy(function($date) {
            return Carbon::parse($date->created_at)->format('Y-W');
        });
        $maxWeeklyWorkouts = $workoutsByWeek->map->count()->max() ?? 0;

        foreach ($logs as $log) {
            $hour = $log->created_at->hour;
            if ($hour < 8) $morningWorkouts++;
            if ($hour >= 20) $nightWorkouts++;
            if ($log->created_at->isWeekend()) $weekendWorkouts++;
            
            if ($log->duration_minutes > $maxDuration) $maxDuration = $log->duration_minutes;

            if ($log->exercises_data && is_array($log->exercises_data)) {
                foreach ($log->exercises_data as $ex) {
                    $uniqueExercises[$ex['name'] ?? 'Unknown'] = true;
                    // Max Weight (Check sets)
                    if (isset($ex['sets']) && is_array($ex['sets'])) {
                        foreach ($ex['sets'] as $set) {
                           if (isset($set['weight']) && $set['weight'] > $maxWeightLifted) {
                               $maxWeightLifted = $set['weight'];
                           }
                        }
                    }
                }
            }
        }
        $uniqueExercisesCount = count($uniqueExercises);

        // --- DB Driven Badges ---
        $allBadges = Badge::all();
        $userBadgeIds = $user->badges()->pluck('badges.id')->toArray();
        $finalBadges = [];
        $newlyUnlocked = [];

        foreach ($allBadges as $badge) {
            $isUnlocked = in_array($badge->id, $userBadgeIds);
            
            $progress = 0;
            $target = $badge->target_value;

            switch ($badge->slug) {
                case 'tester_master': $progress = $stats['totalWorkouts']; break;
                case 'first_steps': $progress = $stats['totalWorkouts']; break;
                case 'gym_rat': $progress = $maxWeeklyWorkouts; break;
                case 'on_fire': $progress = $streak['current']; break;
                case 'iron_habit': $progress = $stats['totalWorkouts']; break;
                case 'dedication': $progress = $stats['totalWorkouts']; break;
                case 'marathon_runner': $progress = $maxDuration; break;
                case 'variety_master': $progress = $uniqueExercisesCount; break;
                case 'human_crane': $progress = $stats['totalVolume']; break;
                case 'hercules': $progress = $stats['totalVolume']; break;
                case 'volume_king': $progress = $stats['totalVolume']; break;
                case 'atlas': $progress = $stats['totalVolume']; break;
                case 'early_bird': $progress = $morningWorkouts; break;
                case 'night_owl': $progress = $nightWorkouts; break;
                case 'weekend_warrior': $progress = $weekendWorkouts; break;
                case 'heavy_weight': $progress = $maxWeightLifted; break;
                case 'beast_mode': $progress = $maxWeightLifted; break;
                default: $progress = 0;
            }

            if (!$isUnlocked && $progress >= $target) {
                $user->badges()->attach($badge->id);
                $isUnlocked = true;
                $newlyUnlocked[] = [
                    'id' => $badge->id,
                    'slug' => $badge->slug,
                    'name' => $badge->name,
                    'icon' => $badge->icon,
                    'description' => $badge->description,
                ];
            }

            $finalBadges[] = [
                'id' => $badge->id,
                'slug' => $badge->slug,
                'name' => $badge->name,
                'description' => $badge->description,
                'icon' => $badge->icon,
                'unlocked' => $isUnlocked,
                'progress' => min($progress, $target),
                'target' => $target,
            ];
        }

        if (!empty($newlyUnlocked)) {
            session()->flash('new_badges', $newlyUnlocked);
        }

        return $finalBadges;
    }

    public function shareAchievement(Request $request)
    {
        $request->validate(['badge_id' => 'required|exists:badges,id']);
        $badge = Badge::find($request->badge_id);
        
        if (!$request->user()->badges->contains($badge->id)) {
            return back()->with('error', 'No has desbloqueado este logro.');
        }

        \App\Models\Post::create([
            'user_id' => $request->user()->id,
            'content' => "¡He desbloqueado el logro {$badge->name}!",
            'type' => 'achievement',
            'metadata' => [
                'badge_slug' => $badge->slug,
                'badge_name' => $badge->name,
                'badge_icon' => $badge->icon,
                'description' => $badge->description
            ]
        ]);

        return back()->with('success', '¡Logro compartido!');
    }

    public function dayDetails(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->user_id);
        $currentUser = $request->user();

        // Verificar permisos
        $isOwnProfile = $currentUser->id === $user->id;
        $areGymPals = false;
        
        if (!$isOwnProfile) {
            $connectionTo = Connection::where('sender_id', $currentUser->id)
                ->where('receiver_id', $user->id)->first();
            $connectionFrom = Connection::where('sender_id', $user->id)
                ->where('receiver_id', $currentUser->id)->first();
            $areGymPals = $connectionTo && $connectionFrom;
        }

        if (!$isOwnProfile && !$areGymPals) {
            return response()->json(['error' => 'No tienes permiso para ver estos detalles.'], 403);
        }

        $logs = WorkoutLog::where('user_id', $user->id)
            ->whereDate('created_at', $request->date)
            ->with(['workout' => function($q) {
                $q->select('id', 'name'); 
            }])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'workout_name' => $log->workout ? $log->workout->name : 'Entrenamiento Personalizado',
                    'duration' => $log->duration_minutes,
                    'volume' => $log->total_volume ?? 0,
                    'exercises_count' => count($log->exercises_data ?? []),
                    'time' => $log->created_at->format('H:i'),
                    'exercises' => array_map(function($ex) {
                        return [
                            'name' => $ex['name'] ?? 'Ejercicio',
                            'sets' => count($ex['sets'] ?? [])
                        ];
                    }, $log->exercises_data ?? [])
                ];
            });

        return response()->json(['logs' => $logs, 'date' => $request->date]);
    }


            

        





    
    private function getProgressCharts($user, $year)
    {
        $targetDate = Carbon::createFromDate($year, 12, 31);
        if ($year == Carbon::now()->year) {
            $targetDate = Carbon::now();
        }
        
        $startDate = $targetDate->copy()->subWeeks(12);
        
        $weeklyVolumeData = WorkoutLog::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $targetDate])
            ->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->created_at)->startOfWeek()->format('M d');
            })
            ->map(function ($logs) {
                $weekVolume = 0;
                foreach ($logs as $log) {
                    if (isset($log->exercises_data) && is_array($log->exercises_data)) {
                        foreach ($log->exercises_data as $exercise) {
                            if (isset($exercise['sets']) && is_array($exercise['sets'])) {
                                foreach ($exercise['sets'] as $set) {
                                    $weight = isset($set['weight']) ? floatval($set['weight']) : 0;
                                    $reps = isset($set['reps']) ? intval($set['reps']) : 0;
                                    $weekVolume += $weight * $reps;
                                }
                            }
                        }
                    }
                }
                return $weekVolume;
            });
            
        $weeklyVolume = [];
        $currentWeek = $startDate->copy()->startOfWeek();
        for ($i = 0; $i < 12; $i++) {
            $weekKey = $currentWeek->format('M d');
            $weeklyVolume[] = [
                'week' => $weekKey,
                'volume' => $weeklyVolumeData[$weekKey] ?? 0
            ];
            $currentWeek->addWeek();
        }
        
        // Frecuencia mensual (Año completo)
        $monthlyFrequencyData = WorkoutLog::where('user_id', $user->id)
            ->whereYear('created_at', $year)
            ->select(
                DB::raw('EXTRACT(MONTH FROM created_at) as month'), 
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->get()
            ->keyBy('month');
            
        $monthlyFrequency = [];
        for ($i = 1; $i <= 12; $i++) {
            $date = Carbon::createFromDate($year, $i, 1);
            $monthlyFrequency[] = [
                'month' => $date->format('M'),
                'count' => $monthlyFrequencyData[$i]->count ?? 0
            ];
        }
        
        return [
            'weeklyVolume' => $weeklyVolume,
            'monthlyFrequency' => $monthlyFrequency,
        ];
    }
    
    private function generateInsights($user, $stats, $streak)
    {
        $insights = [];
        $thisWeek = $stats['workoutsThisWeek'];
        $avg = $stats['avgWeekly'];
        
        if ($thisWeek > $avg && $avg > 0) {
             $insights[] = [
                'type' => 'positive',
                'icon' => '📈',
                'message' => '¡Estás entrenando más de lo habitual esta semana!',
            ];
        }
        
        if ($streak['current'] >= 3) {
            $insights[] = [
                'type' => 'streak',
                'icon' => '🔥',
                'message' => "¡Llevas {$streak['current']} días seguidos entrenando!",
            ];
        }
        
        // GymPal Coach Logic 🤖
        $lastWorkoutDate = $streak['lastWorkout'] ? Carbon::parse($streak['lastWorkout']) : null;
        
        if ($lastWorkoutDate && $lastWorkoutDate->diffInDays(Carbon::now()) > 7) {
            $insights[] = [
                'type' => 'warning',
                'icon' => '😴',
                'message' => 'Hace más de una semana que no entrenas. ¡El descanso es bueno, pero no tanto!',
            ];
        }

        if ($avg > 0 && $thisWeek == 0 && Carbon::now()->dayOfWeek > 3) {
             $insights[] = [
                'type' => 'coach',
                'icon' => '🤖',
                'message' => 'Semana difícil, ¿verdad? Aún estás a tiempo de salvarla con un entreno rápido.',
            ];
        }

        if ($stats['totalWorkouts'] >= 5 && $stats['totalWorkouts'] < 10) {
             $insights[] = [
                'type' => 'goal',
                'icon' => '🎯',
                'message' => '¡Estás muy cerca de completar 10 entrenamientos!',
            ];
        } else if ($stats['totalVolume'] > 8000 && $stats['totalVolume'] < 10000) {
             $insights[] = [
                'type' => 'goal',
                'icon' => '🚜',
                'message' => '¡GymPal Coach calcula que casi llegas a los 10,000 kg movidos!',
            ];
        }

        // Si entrena mucho de noche
        $nightOwlCount = WorkoutLog::where('user_id', $user->id)
            ->whereTime('created_at', '>=', '20:00:00')
            ->count();
            
        if ($nightOwlCount > 5 && $nightOwlCount / ($stats['totalWorkouts'] ?: 1) > 0.5) {
             $insights[] = [
                'type' => 'info',
                'icon' => '🦉',
                'message' => 'Veo que eres un animal nocturno. ¡Asegúrate de dormir bien post-entreno!',
            ];
        }
        
        return $insights;
    }

    public function storeGoal(Request $request)
    {
        $request->validate([
            'type' => 'required|string', // weekly_workouts, monthly_volume, streak
            'target_value' => 'required|integer|min:1',
            'expiry_date' => 'nullable|date',
        ]);

        Goal::create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'target_value' => $request->target_value,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => $request->expiry_date,
        ]);

        return redirect()->back()->with('success', 'Objetivo creado correctamente.');
    }

    public function destroyGoal($id)
    {
        \Log::info('Tentando eliminar objetivo ID: ' . $id);
        $goal = Goal::findOrFail($id);
        
        if ($goal->user_id !== auth()->id()) {
            abort(403);
        }
        
        $goal->delete();

        return redirect()->back()->with('success', 'Objetivo eliminado.');
    }

    private function getGoals($user)
    {
        $goals = Goal::where('user_id', $user->id)
            ->get();
            
        $goalsData = [];
        
        foreach ($goals as $goal) {
            $progress = 0;
            
            // 1. Días de entreno semanal
            if ($goal->type === 'weekly_workouts') {
                $progress = WorkoutLog::where('user_id', $user->id)
                    ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->count();
            
            // 2. Minutos activos semanales (Nuevo - Reemplaza Kilos)
            } else if ($goal->type === 'weekly_minutes') {
                // Estimamos 45 min por sesión o sumamos duración real si existiera
                $logsThisWeek = WorkoutLog::where('user_id', $user->id)
                    ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->get();
                
                // Si tienes campo duration úsalo, si no, estimamos 45 min por log
                // O mejor: contemos ejercicios * 5 min
                foreach ($logsThisWeek as $log) {
                    $estimated = 0;
                    if (isset($log->exercises_data) && is_array($log->exercises_data)) {
                        $estimated = count($log->exercises_data) * 6; // 6 min por ejercicio
                    }
                    if ($estimated == 0) $estimated = 45; // Fallback
                    $progress += $estimated;
                }

            } else if ($goal->type === 'monthly_volume') {
                $logsThisMonth = WorkoutLog::where('user_id', $user->id)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->get();
                
                $totalVolumeFound = 0;
                foreach ($logsThisMonth as $log) {
                    if (isset($log->exercises_data) && is_array($log->exercises_data)) {
                        foreach ($log->exercises_data as $exercise) {
                            if (isset($exercise['sets']) && is_array($exercise['sets'])) {
                                foreach ($exercise['sets'] as $set) {
                                    if (isset($set['completed']) && $set['completed']) {
                                        $totalVolumeFound += (float)($set['weight'] ?? 0) * (int)($set['reps'] ?? 0);
                                    }
                                }
                            }
                        }
                    }
                }
                $progress = $totalVolumeFound;

            } else if ($goal->type === 'max_weight') {
                $prRecord = $this->getPersonalRecords($user);
                $progress = !empty($prRecord) ? $prRecord[0]['maxWeight'] : 0;
                
            } else if ($goal->type === 'streak') {
                $streakData = $this->calculateStreak($user);
                $progress = $streakData['current'];
                
            // 3. Madrugador (Nuevo)
            } else if ($goal->type === 'early_bird') {
                 $progress = WorkoutLog::where('user_id', $user->id)
                    ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->whereTime('created_at', '<', '09:00:00')
                    ->count();
            }
            
            $isCompleted = ($goal->status === 'completed') || ($progress >= $goal->target_value);

            $goalsData[] = [
                'id' => $goal->id,
                'type' => $goal->type,
                'target_value' => $goal->target_value,
                'current_value' => round($progress),
                'completed' => $isCompleted,
                'end_date' => $goal->end_date,
            ];
        }
        
        return $goalsData;
    }
}
