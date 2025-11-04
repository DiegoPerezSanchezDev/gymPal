<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /**
     * Obtener todas las notificaciones del usuario autenticado
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $unreadOnly = $request->boolean('unread_only', false);

        $query = $user->notifications();

        if ($unreadOnly) {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate(20);

        // Si es una petición AJAX, devolver JSON con paginación
        if ($request->wantsJson()) {
            return response()->json([
                'data' => $notifications->items(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
                'unread_count' => $user->unreadNotifications()->count(),
            ]);
        }

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Obtener contador de notificaciones no leídas (API)
     */
    public function unreadCount()
    {
        $count = Auth::user()->unreadNotifications()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Marcar una notificación como leída
     */
    public function markAsRead(Notification $notification)
    {
        // Verificar que la notificación pertenece al usuario autenticado
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notificación marcada como leída',
            'unread_count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'message' => 'Todas las notificaciones marcadas como leídas',
            'unread_count' => 0,
        ]);
    }

    /**
     * Eliminar una notificación
     */
    public function destroy(Notification $notification)
    {
        // Verificar que la notificación pertenece al usuario autenticado
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $notification->delete();

        return response()->json([
            'message' => 'Notificación eliminada',
            'unread_count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }
}
