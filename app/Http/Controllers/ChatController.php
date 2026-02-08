<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Conversation; // Importa el modelo Conversation
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;

class ChatController extends Controller
{
    /**
     * Muestra la lista de conversaciones del usuario autenticado.
     */
    public function index()
    {
        // Simulate latency in development for testing Skeletons
        if (app()->environment('local') && request()->has('simulate_latency')) {
            sleep(1);
        }

        $currentUser = Auth::user();

        // Obtener las conversaciones del usuario actual
        // Cargar también los participantes de cada conversación y el último mensaje
        $conversations = $currentUser->conversations() // Usa la relación definida en el modelo User
            ->with([
                // Cargar los participantes de la conversación, EXCLUYENDO al usuario actual
                'users' => function ($query) use ($currentUser) {
                    $query->where('users.id', '!=', $currentUser->id)
                          ->select(['users.id', 'users.name', 'users.username', 'users.profile_picture_url']); // Selecciona solo los campos necesarios
                },
                // Cargar el último mensaje de la conversación
                'latestMessage' => function ($query) {
                    $query->select(['conversation_id', 'user_id', 'body', 'created_at']) // Campos necesarios del mensaje
                          ->with(['user:id,name']); // Cargar el remitente del último mensaje (solo id y name)
                }
            ])
            // Ordenar las conversaciones por la fecha del último mensaje (si tienes 'last_message_at' en 'conversations')
            // o por la fecha de creación/actualización de la tabla pivote o la conversación misma.
            // Si no tienes last_message_at, podrías ordenar por created_at o updated_at de la conversación.
            ->orderByDesc('conversations.last_message_at') // O 'conversations.updated_at'
            ->paginate(15); // Paginar las conversaciones

        // Transformar las conversaciones para el frontend si es necesario
        // Por ejemplo, determinar el "nombre" o "avatar" de la conversación (para chats 1-a-1, será el del otro usuario)
        $conversations->through(function ($conversation) use ($currentUser) {
            $otherUser = $conversation->users->first(); // En un chat 1-a-1, users solo tendrá al otro usuario
            return [
                'id' => $conversation->id,
                'chat_title' => $otherUser ? $otherUser->name : 'Chat Desconocido',
                'chat_avatar' => $otherUser ? ($otherUser->profile_picture_url ?: 'https://ui-avatars.com/api/?name='.urlencode($otherUser->name).'&background=random&color=fff') : 'https://ui-avatars.com/api/?name=G+P&background=random&color=fff',
                'other_user_username' => $otherUser ? $otherUser->username : null, // Para el enlace al chat individual
                'last_message_body' => $conversation->latestMessage ? Str::limit($conversation->latestMessage->body, 30) : 'No hay mensajes...',
                'last_message_sender' => $conversation->latestMessage && $conversation->latestMessage->user ? ($conversation->latestMessage->user_id === $currentUser->id ? 'Tú' : $conversation->latestMessage->user->name) : '',
                'last_message_at_human' => $conversation->latestMessage ? $conversation->latestMessage->created_at->diffForHumans() : ($conversation->updated_at ? $conversation->updated_at->diffForHumans() : ''),
                'unread_count' => 0, // Implementar lógica de no leídos después
            ];
        });


        return Inertia::render('Chat/Index', [
            'title' => 'Mis Conversaciones',
            'conversations' => $conversations,
            'isLoginPage' => false,
            'isRegisterPage' => false,
        ]);
    }

    /**
     * Obtiene el total de mensajes no leídos para el usuario autenticado.
     */
    public function unreadCount()
    {
        $user = Auth::user();
        
        $count = \App\Models\Message::whereHas('conversation', function ($query) use ($user) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        })
        ->where('user_id', '!=', $user->id)
        ->whereNull('read_at')
        ->count();

        return response()->json(['count' => $count]);
    }

    public function show(User $user) // $otherUser es el perfil que se visita/con quien se quiere chatear
{
    if (!$user->exists) {
        // Loguear esto es importante para saber que está pasando
        Log::warning("ChatController@show: Se intentó acceder al chat con un usuario que no existe en la BD (ID de la URL probablemente inválido). Debería ser un 404 por RBM.");
        abort(404, 'Usuario no encontrado.'); // Forzar un 404
    }

    $currentUser = Auth::user();

    if ($currentUser->id === $user->id) {
        return redirect()->route('chat.index')->with('error_toast', 'No puedes chatear contigo mismo.');
    }

    // Intentar encontrar una conversación existente entre estos dos usuarios y nadie más.
    $conversation = Conversation::query()
        // Asegurar que el currentUser participa
        ->whereHas('users', function ($q) use ($currentUser) {
            $q->where('users.id', $currentUser->id);
        })
        // Asegurar que el otherUser participa
        ->whereHas('users', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        })
        // Asegurar que la conversación tenga EXACTAMENTE 2 participantes
        ->has('users', '=', 2)
        ->first();

    if (!$conversation) {
        // Crear una nueva conversación si no existe una exclusiva entre ellos
        $conversation = DB::transaction(function () use ($currentUser, $user) {
            $newConversation = Conversation::create(['last_message_at' => now()]);
            $newConversation->users()->attach([$currentUser->id, $user->id]);
            return $newConversation;
        });
    }

    // Marcar mensajes como leídos (si el usuario actual no es el remitente)
    $conversation->messages()
        ->where('user_id', $user->id)
        ->whereNull('read_at')
        ->update(['read_at' => now()]);

    $messages = $conversation->messages()
        ->with('user:id,name,username,profile_picture_url')
        ->latest() // Ordena por created_at DESC (más recientes primero)
        ->paginate(25);

    return Inertia::render('Chat/Show', [
        'title' => 'Chat con ' . $user->name,
        'chatWithUser' => $user->only(['id', 'name', 'username', 'profile_picture_url']),
        'conversationId' => $conversation->id,
        'messages' => $messages, //
    ]);
}

    // Método para guardar un nuevo mensaje (se llamaría vía POST desde Chat/Show.vue)
    public function storeMessage(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'body' => 'nullable|string|max:2000',
            'image' => 'nullable|file|image|mimes:jpeg,jpg,png,gif|max:5120', // 5MB máx
        ]);
        
        $currentUser = Auth::user();

        if (!$conversation->users()->where('user_id', $currentUser->id)->exists()) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        // Preparar datos del mensaje
        $messageData = [
            'user_id' => $currentUser->id,
            'body' => $validated['body'] ?? null,
        ];

        try {
            // Manejar imagen si se envió
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('chat-images', 'public');
                $messageData['image_url'] = $path;
            }

            // Validar que haya al menos body o imagen
            if (empty($messageData['body']) && empty($messageData['image_url'])) {
                return response()->json(['message' => 'Debes enviar un mensaje o una imagen.'], 422);
            }

            $message = $conversation->messages()->create($messageData);
        } catch (\Exception $e) {
            \Log::error('Error sending message: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno al enviar el mensaje: ' . $e->getMessage()], 500);
        }

        $conversation->update(['last_message_at' => now()]); // Actualiza el timestamp

        // Obtener el otro usuario de la conversación (el destinatario)
        $recipient = $conversation->users()
            ->where('users.id', '!=', $currentUser->id)
            ->first();

        // Crear notificación para el destinatario (solo si existe y no es el mismo usuario)
        if ($recipient && $recipient->id !== $currentUser->id) {
            // Nota: En una implementación real, deberías verificar si el usuario está viendo el chat
            // Por ahora, notificamos siempre. Se puede mejorar con broadcasting o verificando la página actual.
            NotificationService::notifyNewMessage($recipient, $message, $currentUser);
        }

        // event(new NewMessageSent($message->load('user:id,name,profile_picture_url'))); // Para broadcasting

        // Devolver el mensaje con su usuario cargado para el frontend
        return response()->json($message->load('user:id,name,username,profile_picture_url'));
    }
}