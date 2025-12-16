<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class StoryController extends Controller
{
    /**
     * Obtiene las historias activas de los amigos (y propias) agrupadas por usuario.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // 1. Obtener IDs de amigos (gympals + following si existiera ese concepto separado)
        // Usamos getGymPalsAttribute que ya arreglamos
        $friendIds = $user->gym_pals->pluck('id')->push($user->id); // Incluirse a uno mismo

        // 2. Traer usuarios con sus historias activas
        $usersWithStories = User::whereIn('id', $friendIds)
            ->whereHas('activeStories') // Solo usuarios que tengan algo que contar
            ->with(['activeStories' => function ($query) use ($user) {
                // Eager load de 'viewers' filtrado solo para el usuario actual para saber si ya la vio
                // Esto optimiza el atributo 'is_viewed'
                 $query->withExists(['viewers as is_viewed_by_me' => function ($q) use ($user) {
                     $q->where('user_id', $user->id);
                 }]);
            }])
            ->get();

        // 3. Transformar y ordenar
        // Prioridad: 
        //  - Usuarios con al menos una historia NO vista (active ring)
        //  - Usuarios con todas las historias vistas
        $formattedStories = $usersWithStories->map(function ($u) {
            $stories = $u->activeStories->map(function ($s) {
                return [
                    'id' => $s->id,
                    'image_url' => Storage::url($s->image_path),
                    'content' => $s->content,
                    'created_at' => $s->created_at,
                    'is_viewed' => $s->is_viewed_by_me,
                    'type' => $s->type,          // Nuevo
                    'metadata' => $s->metadata,  // Nuevo
                ];
            });

            // ¿Tiene alguna sin ver?
            $hasUnseen = $stories->contains('is_viewed', false);

            return [
                'user' => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username,
                    'avatar_url' => $u->profile_picture_url ? Storage::url($u->profile_picture_url) : null,
                ],
                'stories' => $stories,
                'has_unseen' => $hasUnseen,
            ];
        })->sortByDesc('has_unseen') // Primero los que tienen novedades
          ->values();

        return response()->json($formattedStories);
    }

    /**
     * Comprobar si hay actividad reciente para sugerir Smart Story.
     */
    public function checkRecentActivity(Request $request)
    {
        // Buscar workout log de las últimas 24h
        $latestLog = Auth::user()->workoutLogs()
            ->where('created_at', '>', now()->subHours(24))
            ->latest()
            ->with(['workout', 'exercises'])
            ->first();

        if (!$latestLog) {
            return response()->json(['has_activity' => false]);
        }

        // Calcular estadísticas rápidas para la card
        $totalVolume = 0;
        $totalSets = 0;
        foreach ($latestLog->exercises as $exercise) {
            foreach ($exercise->sets as $set) {
                if ($set['weight'] && $set['reps']) {
                    $totalVolume += $set['weight'] * $set['reps'];
                }
                $totalSets++;
            }
        }

        return response()->json([
            'has_activity' => true,
            'workout_log' => [
                'id' => $latestLog->id,
                'name' => $latestLog->workout->name ?? 'Entreno Libre',
                'duration' => $latestLog->duration_minutes . ' min',
                'volume' => $totalVolume,
                'sets' => $totalSets,
                'date' => $latestLog->created_at->diffForHumans(),
            ]
        ]);
    }

    /**
     * Subir una nueva historia.
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240', // 10MB max
            'content' => 'nullable|string|max:255',
            'workout_log_id' => 'nullable|exists:workout_logs,id',
            'type' => 'nullable|string|in:standard,workout_data',
            'metadata' => 'nullable|array'
        ]);

        $path = $request->file('image')->store('stories', 'public');
        
        $type = $request->type ?? 'standard';
        
        // Inicializar metadata con lo que venga del request (ej: posición texto) o array vacío
        $metadata = $request->metadata ?? [];
        if (!is_array($metadata)) $metadata = [];

        $workoutLogId = $request->workout_log_id;

        // Si es Smart Story, generar metadata automática y FUSIONAR
        if ($workoutLogId) {
            $log = Auth::user()->workoutLogs()->find($workoutLogId);
            if ($log) {
                $type = 'workout_data';
                
                // Recalcular volumen
                $totalVolume = 0;
                foreach ($log->exercises as $exercise) {
                    foreach ($exercise->sets as $set) {
                        if (isset($set['weight'], $set['reps'])) {
                            $totalVolume += $set['weight'] * $set['reps'];
                        }
                    }
                }

                $workoutData = [
                    'workout_name' => $log->workout->name ?? 'Entrenamiento',
                    'duration' => $log->duration_minutes,
                    'volume' => $totalVolume,
                    'exercises_count' => count($log->exercises),
                    'intensity' => 'high',
                ];
                
                // Fusionar: workoutData sobreescribe claves si chocan, pero mantenemos text_position
                $metadata = array_merge($metadata, $workoutData);
            }
        }

        $story = Auth::user()->stories()->create([
            'image_path' => $path,
            'content' => $request->content,
            'workout_log_id' => $workoutLogId,
            'type' => $type,
            'metadata' => $metadata,
            'expires_at' => now()->addHours(24),
        ]);

        return back()->with('success', '¡Historia subida!');
    }

    /**
     * Marcar historia como vista.
     */
    public function markAsViewed(Story $story)
    {
        // Evitar duplicados
        $story->viewers()->syncWithoutDetaching([Auth::id()]);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Eliminar historia (solo dueño)
     */
    public function destroy(Story $story)
    {
        if ($story->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Borrar archivo
        Storage::disk('public')->delete($story->image_path);
        
        $story->delete();
        
        return back()->with('success', 'Historia eliminada.');
    }
}
