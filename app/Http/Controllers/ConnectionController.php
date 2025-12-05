<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Services\NotificationService;

class ConnectionController extends Controller
{
    /**
     * Enviar solicitud de conexión
     */
    public function store(User $user)
    {
        $currentUser = Auth::user();
        $targetUser = $user;

        if ($currentUser->id === $targetUser->id) {
            return redirect()->back()->with('error', 'No puedes conectarte contigo mismo.');
        }

        // Verificar si YO ya le envié una conexión
        $myConnection = Connection::where('sender_id', $currentUser->id)
            ->where('receiver_id', $targetUser->id)
            ->first();

        if ($myConnection) {
            if ($myConnection->status === 'accepted') {
                return redirect()->back()->with('info', 'Ya estás conectado con este usuario.');
            }
            if ($myConnection->status === 'pending') {
                return redirect()->back()->with('info', 'Ya has enviado una solicitud a este usuario.');
            }
        }

        // Verificar si ÉL me envió una conexión
        $hisConnection = Connection::where('sender_id', $targetUser->id)
            ->where('receiver_id', $currentUser->id)
            ->first();

        if ($hisConnection && $hisConnection->status === 'pending') {
            // Si él me envió solicitud pendiente, la acepto automáticamente
            $hisConnection->status = 'accepted';
            $hisConnection->save();
            
            // Notificar al otro usuario
            NotificationService::notifyConnectionAccepted($hisConnection->sender, $hisConnection, $currentUser);
            
            return redirect()->back()->with('success', '¡Conexión establecida! Ahora sois GymPals.');
        }

        // Si él me sigue (accepted) pero yo no lo sigo, crear mi conexión
        // O si no hay ninguna conexión, crear nueva
        $connection = Connection::create([
            'sender_id'   => $currentUser->id,
            'receiver_id' => $targetUser->id,
        ]);

        // Crear notificación para el receptor
        NotificationService::notifyConnectionRequest($targetUser, $connection, $currentUser);

        return redirect()->back()->with('success', 'Solicitud de conexión enviada.');
    }

    /**
     * Muestra la página "Mis Conexiones" con 3 tabs
     */
    public function index()
    {
        $currentUser = User::find(Auth::id());
        
        // GymPals - Conexiones aceptadas (mutuamente conectados)
        $gymPals = $currentUser->gym_pals->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'profile_picture_url' => $user->profile_picture_url,
                'experience_level' => $user->experience_level,
            ];
        });

        // Solicitudes pendientes que HE ENVIADO
        $pendingSent = $currentUser->pending_sent->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'profile_picture_url' => $user->profile_picture_url,
                'experience_level' => $user->experience_level,
            ];
        });

        // Solicitudes pendientes que HE RECIBIDO
        $pendingReceived = $currentUser->pending_received->map(function ($user) use ($currentUser) {
            // Añadir connection_id para poder aceptar/rechazar
            $connection = Connection::where('sender_id', $user->id)
                ->where('receiver_id', $currentUser->id)
                ->where('status', 'pending')
                ->first();
                
            return [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'profile_picture_url' => $user->profile_picture_url,
                'experience_level' => $user->experience_level,
                'connection_id' => $connection ? $connection->id : null,
            ];
        });

        // Contadores para los tabs
        $counts = [
            'gymPals' => $gymPals->count(),
            'pendingSent' => $pendingSent->count(),
            'pendingReceived' => $pendingReceived->count(),
        ];
        
        $tab = request()->input('tab', 'gymPals');

        return Inertia::render('Connections/Index', [
            'gymPals' => $gymPals,
            'pendingSent' => $pendingSent,
            'pendingReceived' => $pendingReceived,
            'currentTab' => $tab,
            'counts' => $counts,
        ]);
    }

    /**
     * Aceptar una solicitud de conexión
     */
    public function accept(Connection $connection)
    {
        abort_if(Auth::id() !== $connection->receiver_id, 403, 'Acción no autorizada.');

        $connection->status = 'accepted';
        $connection->save();
        
        $connection->load('sender');
        $accepter = Auth::user();
        
        NotificationService::notifyConnectionAccepted($connection->sender, $connection, $accepter);

        return redirect()->back()->with('success', '¡Conexión aceptada!');
    }

    /**
     * Rechazar una solicitud de conexión
     */
    public function reject(Connection $connection)
    {
        abort_if(Auth::id() !== $connection->receiver_id, 403, 'Acción no autorizada.');

        $connection->delete();

        return redirect()->back()->with('success', 'Solicitud rechazada.');
    }

    /**
     * Eliminar una conexión
     */
    public function destroy(Connection $connection)
    {
        abort_if(
            $connection->sender_id !== Auth::id() && $connection->receiver_id !== Auth::id(),
            403,
            'No tienes permiso para realizar esta acción.'
        );

        $connection->delete();

        return redirect()->back()->with('success', 'Conexión eliminada correctamente.');
    }

    /**
     * Obtiene las conexiones aceptadas del usuario actual (para API)
     */
    public function getGymPals()
    {
        $currentUser = Auth::user();
        
        // Obtener gym pals y asegurar unicidad por ID
        $gymPals = $currentUser->gym_pals->unique('id')->values()->map(function ($gymPal) {
            return [
                'id' => $gymPal->id,
                'name' => $gymPal->name,
                'username' => $gymPal->username,
                'profile_picture_url' => $gymPal->profile_picture_url,
            ];
        });

        return response()->json($gymPals);
    }
}