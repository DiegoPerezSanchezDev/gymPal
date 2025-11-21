<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
class FeedController extends Controller
{
    public function index(Request $request)
    {
        // Simular latencia en desarrollo para probar Skeletons
        if (app()->environment('local') && $request->has('simulate_latency')) {
            sleep(1);
        }
        
        $user = Auth::user();
        $activeTab = $request->input('tab', 'siguiendo');

        $postsQuery = Post::query()
        ->with([
            'user', 
            'latestLikers',
            'latestComments.user'
        ])
        ->withCount(['likers','comments']);
    
        switch ($activeTab) {
            case 'populares':
                // Ordenar por likes y luego por fecha
                $postsQuery->orderByDesc('likers_count')->orderByDesc('created_at');
                break;
    
            case 'cerca':
                // Lógica futura y avanzada para geolocalización.
                // Por ahora, mostramos todos.
                $postsQuery->latest();
                break;
    
            case 'siguiendo':
            default:
                // mostrar posts de GymPals y del propio usuario.
                $gymPalIds = $user->gym_pals->pluck('id');
                $idsToQuery = $gymPalIds->push($user->id);
                $postsQuery->whereIn('user_id', $idsToQuery)->latest();
                break;
        }
    
        $posts = $postsQuery->paginate(10)->withQueryString();
    
        return Inertia::render('Feed', [
            'posts' => $posts,
            'title' => 'Tu Feed',
            'activeTab' => $activeTab,
        ]);
    }
}
