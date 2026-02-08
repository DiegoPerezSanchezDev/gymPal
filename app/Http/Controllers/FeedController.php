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
            'user.gyms', 
            'workoutLog',
            'workout.exercises',
            'latestLikers',
            'latestComments.user'
        ])
        ->withCount(['likers','comments']);
    
        $userId = $user->id;
        
        // IDs de personas que YO sigo (conexiones que YO envié y fueron aceptadas)
        $iFollowIds = \App\Models\Connection::where('sender_id', $userId)
            ->where('status', 'accepted')
            ->pluck('receiver_id');
        
        // IDs de personas que ME siguen (conexiones que ME enviaron y acepté)
        $followingMeIds = \App\Models\Connection::where('receiver_id', $userId)
            ->where('status', 'accepted')
            ->pluck('sender_id');
        
        // GymPals = Conexiones mutuas (yo los sigo Y ellos me siguen)
        $gymPalIds = $iFollowIds->intersect($followingMeIds);

        switch ($activeTab) {
            case 'populares':
                // Populares: Gente que YO NO sigo (para que aparezcan los "Te sigue")
                // Excluimos solo a los que YO sigo (iFollowIds) y a mí mismo
                $postsQuery->whereNotIn('posts.user_id', $iFollowIds->concat([$userId]))
                           ->orderByDesc('likers_count')
                           ->orderByDesc('posts.created_at');
                break;
    
            case 'cerca':
                // Cerca: Gente cercana que YO NO sigo (consistente con Populares)
                $postsQuery->whereNotIn('posts.user_id', $iFollowIds->concat([$userId]));
                
                if ($user->latitude && $user->longitude) {
                    $lat = $user->latitude;
                    $lon = $user->longitude;
                    
                    // Join y select específico para evitar colisiones y mantener withCount
                    $postsQuery->join('users', 'posts.user_id', '=', 'users.id')
                        ->addSelect('posts.*') // addSelect mantiene los counts
                        ->selectRaw("
                            (6371 * acos(
                                cos(radians(?)) * cos(radians(users.latitude)) * cos(radians(users.longitude) - radians(?)) + 
                                sin(radians(?)) * sin(radians(users.latitude))
                            )) AS distance", [$lat, $lon, $lat])
                        ->whereNotNull('users.latitude')
                        ->whereNotNull('users.longitude');

                    // Ordenamos por el alias 'distance' que ahora está en el SELECT
                    $postsQuery->whereRaw("(6371 * acos(cos(radians(?)) * cos(radians(users.latitude)) * cos(radians(users.longitude) - radians(?)) + sin(radians(?)) * sin(radians(users.latitude)))) <= 20", [$lat, $lon, $lat])
                               ->orderBy('distance', 'asc')
                               ->orderByDesc('posts.created_at');
                } else {
                    $postsQuery->whereRaw('1=0');
                }
                break;
    
            case 'siguiendo':
            default:
                // Siguiendo: Posts de gente que YO sigo + MIS PROPIOS posts
                $postsQuery->where(function($q) use ($iFollowIds, $userId) {
                    $q->whereIn('posts.user_id', $iFollowIds)
                      ->orWhere('posts.user_id', $userId); // Incluir mis propios posts
                })->orderByDesc('posts.created_at');
                break;
        }
    
        $posts = $postsQuery->paginate(10)->withQueryString();

        // Añadir meta y atributos a cada post
        $posts->getCollection()->transform(function ($post) use ($user, $followingMeIds, $gymPalIds, $iFollowIds) {
            $post->is_liked = $post->likers()->where('user_id', $user->id)->exists();
            $post->is_saved = $user->savedPosts()->where('post_id', $post->id)->exists();
            $post->user->is_following_me = $followingMeIds->contains($post->user_id); // Me sigue
            $post->user->is_gym_pal = $gymPalIds->contains($post->user_id); // Conexión mutual
            $post->user->is_following = $iFollowIds->contains($post->user_id); // Yo lo sigo
            return $post;
        });
    
        return Inertia::render('Feed', [
            'posts' => $posts,
            'title' => 'Tu Feed',
            'activeTab' => $activeTab,
        ]);
    }
}
