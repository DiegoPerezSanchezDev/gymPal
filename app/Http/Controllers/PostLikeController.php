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
            
            if (!$user) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }
            
            // Log para debug
            Log::info('PostLikeController: Usuario autenticado', ['user_id' => $user->id]);
            
            // Verificar si el usuario ya dio like
            $wasLiked = $user->likedPosts()->where('post_id', $post->id)->exists();
            
            // Si el ID del usuario ya está en la tabla pivote, lo quita.
            // Si no está, lo añade.
            $user->likedPosts()->toggle($post->id);
            
            // Actualizar el contador en la tabla posts
            $post->likes_count = $post->likers()->count();
            $post->save();
            
            // Recargar el post para obtener el contador actualizado
            $post->refresh();

            // Devolvemos una respuesta JSON que el frontend pueda usar
            return response()->json([
                'is_liked' => $user->likedPosts()->where('post_id', $post->id)->exists(),
                'likes_count' => $post->likes_count,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en PostLikeController', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}
