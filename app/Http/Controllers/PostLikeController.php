<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PostLikeController extends Controller
{
    public function toggleLike(Post $post)
    {
        try {
            $user = Auth::user();
    
            // Protege por si no es instancia de tu modelo
            if (!$user instanceof \App\Models\User) {
                $user = \App\Models\User::find($user->id);
            }
    
            if (!$user) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }
    
            $wasLiked = $user->likedPosts()->where('post_id', $post->id)->exists();
    
            $user->likedPosts()->toggle($post->id);
    
            $post->likes_count = $post->likers()->count();
            $post->save();
            $post->refresh();
    
            $latestLikers = $post->likers()
                ->orderByDesc('post_like.created_at')
                ->take(3)
                ->get(['users.id', 'users.name', 'users.username', 'users.profile_picture_url']);
    
            return response()->json([
                'is_liked' => $user->likedPosts()->where('post_id', $post->id)->exists(),
                'likes_count' => $post->likes_count,
                'latest_likers' => $latestLikers,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en PostLikeController', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}
