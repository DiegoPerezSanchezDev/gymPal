<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Post; 
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Para manejar la subida de archivos
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;

class PostController extends Controller
{
    public function create()
    {
        return Inertia::render('Posts/Create', [
            'title' => 'Crear Nueva Publicación',
            'isLoginPage' => false,
            'isRegisterPage' => false,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'content' => 'nullable|string|max:1000', // Contenido puede ser nulo si hay imagen
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Imagen opcional, tipos y tamaño
        ]);

        // Asegurarse de que al menos uno de los dos esté presente
        if (empty($validatedData['content']) && !$request->hasFile('image')) {
            return back()->withErrors(['content' => 'La publicación debe tener contenido o una imagen.']);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            // Guardar la imagen en public/storage/posts (crea el enlace simbólico con `php artisan storage:link`)
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        

        $user = Auth::user();

        if (!$user instanceof \App\Models\User) {
            dd('Auth::user() no es una instancia de App\Models\User. Es: ' . get_class($user));
        }
        if (!method_exists($user, 'posts')) {
            dd('El método posts() NO existe en la instancia de ' . get_class($user));
        }
        
        // Crear el post en la base de datos
        $user->posts()->create([ // Asume que el modelo User tiene una relación 'post()'
            'content' => $validatedData['content'] ?? null,
            'image_path' => $imagePath, // Guarda la ruta de la imagen
        ]);

        // Redirigir de vuelta al feed con un mensaje de éxito (opcional)
        // Inertia.js maneja la redirección si haces un return redirect()->...
        // return redirect()->route('feed.index')->with('success', '¡Publicación creada!');

        return redirect()->route('feed.index'); // O a donde quieras ir después de crear
                                             // Si quieres quedarte en la misma página y mostrar
                                             // un mensaje de éxito, podrías hacer:
                                             // return back()->with('success_toast', '¡Publicación creada!');
}

    public function show(Post $post)
    {
        $post->load(['user', 'comments.user']);
        return \Inertia\Inertia::render('Posts/Show', ['post' => $post]);
    }

    public function getLikers(Post $post)
    {
        $likers = $post->likers()
            ->select('users.id', 'users.name', 'users.username', 'users.profile_picture_url')
            ->paginate(20);

        return response()->json($likers);
    }

    public function destroy(Post $post)
    {
        if (Auth::id() != $post->user_id) {
            return back()->withErrors(['message' => 'No tienes permiso para eliminar este post.']);
        }
        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }
        $post->delete();

        return back()->with('success_toast', 'Publicación eliminada.');
    }

    /**
     * Comparte un post con otro usuario enviándolo como mensaje
     */
    public function share(Request $request, Post $post)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
        ]);

        $currentUser = Auth::user();
        $recipient = User::findOrFail($validated['recipient_id']);
        
        // Cargar la relación del usuario del post
        $post->load('user');

        // Verificar que el usuario esté conectado con el destinatario
        $isConnected = $currentUser->gym_pals->contains('id', $recipient->id);
        
        if (!$isConnected) {
            return response()->json(['message' => 'Solo puedes compartir con tus GymPals.'], 403);
        }

        // Buscar o crear conversación entre los dos usuarios
        $conversation = \App\Models\Conversation::query()
            ->whereHas('users', function ($q) use ($currentUser) {
                $q->where('users.id', $currentUser->id);
            })
            ->whereHas('users', function ($q) use ($recipient) {
                $q->where('users.id', $recipient->id);
            })
            ->withCount('users')
            ->having('users_count', '=', 2)
            ->first();

        if (!$conversation) {
            $conversation = \App\Models\Conversation::create(['last_message_at' => now()]);
            $conversation->users()->attach([$currentUser->id, $recipient->id]);
        }

        // Cargar datos adicionales del post para la metadata
        $post->load(['user', 'latestLikers']);
        
        // Crear mensaje con el post compartido usando tipo y metadata
        $postUrl = route('posts.show', $post->id);
        $messageBody = "📌 Compartí una publicación contigo";

        $message = $conversation->messages()->create([
            'user_id' => $currentUser->id,
            'body' => $messageBody,
            'type' => \App\Models\Message::TYPE_SHARED_POST,
            'metadata' => [
                'post_id' => $post->id,
                'post_url' => $postUrl,
                'post_author_name' => $post->user->name,
                'post_author_username' => $post->user->username,
                'post_author_profile_picture' => $post->user->profile_picture_url,
                'post_content' => $post->content,
                'post_image_path' => $post->image_path,
                'post_created_at' => $post->created_at->toISOString(),
                'post_likes_count' => $post->likes_count ?? 0,
            ],
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Crear notificación para el destinatario (solo si no es el dueño del post)
        if ($recipient->id !== $post->user_id) {
            NotificationService::notifyPostShared($recipient, $post, $currentUser);
        }

        return response()->json([
            'message' => 'Post compartido exitosamente.',
            'conversation_id' => $conversation->id,
        ], 201);
    }
    
}
