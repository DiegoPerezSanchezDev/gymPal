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
        
        $user = Auth::user();
        $activeTab = $request->input('tab', 'siguiendo');

        $postsQuery = Post::query()
        ->with([
            'user', 
            'latestLikers',
            'latestComments.user'
        ])
        ->withCount(['likers','comments'])->latest();
    
        switch ($activeTab) {
            case 'populares':
                // Lógica futura: ordenar por likes. Por ahora, mostramos todos ordenados por fecha.
                // Cuando implementemos los likes, aquí pondremos ->orderBy('likes_count', 'desc')
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
                $postsQuery->whereIn('user_id', $idsToQuery);
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
