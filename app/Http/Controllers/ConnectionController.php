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
     * Almacena una nueva solicitud de conexión.
     */
    public function store(User $user)
    {
        $sender = Auth::user();
        $receiver = $user;

        if ($sender->id === $receiver->id) {
            return response()->json(['message' => 'No puedes conectarte contigo mismo.'], 422);
        }

        // Verificar si YA existe una solicitud del sender al receiver
        $existingConnection = Connection::where('sender_id', $sender->id)
            ->where('receiver_id', $receiver->id)
            ->first();

        if ($existingConnection) {
            return response()->json(['message' => 'Ya has enviado una solicitud a este usuario.'], 409);
        }
        
        $connection = Connection::create([
            'sender_id'   => $sender->id,
            'receiver_id' => $receiver->id,
        ]);

        // Crear notificación para el receptor
        NotificationService::notifyConnectionRequest($receiver, $connection, $sender);

        return redirect()->back()->with('success', 'Solicitud de conexión enviada.');
    }

    /**
     * Muestra la página "Mis Conexiones".
     */
    public function index()
    {
        // Simulate latency in development for testing Skeletons
        if (app()->environment('local') && request()->has('simulate_latency')) {
            sleep(1);
        }

        $currentUser = User::find(Auth::id());
        $currentUser->load('fitnessInterests');
        $currentUserInterestIds = $currentUser->fitnessInterests->pluck('id');

        //Obtener solicitudes PENDIENTES que he recibido
        $pendingRequests = Connection::where('receiver_id', $currentUser->id)
                                    ->where('status', 'pending')
                                    ->with(['sender' => function($query) {
                                        $query->select('id', 'name', 'username', 'profile_picture_url', 'experience_level');
                                    }, 'sender.fitnessInterests'])
                                    ->orderBy('created_at', 'desc')
                                    ->get()
                                    ->map(function ($request) use ($currentUserInterestIds) {
                                        $commonInterests = $request->sender->fitnessInterests->whereIn('id', $currentUserInterestIds);
                                        $request->sender->common_interests_count = $commonInterests->count();
                                        $request->sender->common_interests_list = $commonInterests->pluck('name')->take(2);
                                        return $request;
                                    });

        //Obtener conexiones ACEPTADAS
        // Obtener conexiones ACEPTADAS donde el usuario es sender
        $sent = Connection::where('sender_id', $currentUser->id)
            ->where('status', 'accepted')
            ->with('receiver:id,name,username,profile_picture_url')
            ->get();

        // Obtener conexiones ACEPTADAS donde el usuario es receiver
        $received = Connection::where('receiver_id', $currentUser->id)
            ->where('status', 'accepted')
            ->with('sender:id,name,username,profile_picture_url')
            ->get();

        // Crear un array único de GymPals para evitar duplicados
        $gymPalsMap = [];

        // Procesar enviadas
        foreach ($sent as $conn) {
            $userId = $conn->receiver_id;
            if (!isset($gymPalsMap[$userId])) {
                $friend = $conn->receiver;
                $friend->connection_id = $conn->id;
                $gymPalsMap[$userId] = $friend;
            }
        }

        // Procesar recibidas (si ya existe, es bidireccional, pero solo mostramos una vez)
        foreach ($received as $conn) {
            $userId = $conn->sender_id;
            if (!isset($gymPalsMap[$userId])) {
                $friend = $conn->sender;
                $friend->connection_id = $conn->id;
                $gymPalsMap[$userId] = $friend;
            }
        }

        $gymPals = collect(array_values($gymPalsMap));

        return Inertia::render('Connections/Index', [
            'pendingRequests' => $pendingRequests,
            'gymPals' => $gymPals,
        ]);
    }

    /**
     * Acepta una solicitud de conexión pendiente.
     */
    public function accept(Connection $connection)
    {
        // Medida de seguridad
        abort_if(Auth::id() !== $connection->receiver_id, 403, 'Acción no autorizada.');

        // Actualizamos el estado a 'accepted'
        $connection->status = 'accepted';
        $connection->save();
        
        // Cargar el sender para la notificación
        $connection->load('sender');
        $accepter = Auth::user();
        
        // Crear notificación para el usuario que envió la solicitud
        NotificationService::notifyConnectionAccepted($connection->sender, $connection, $accepter);

        // Redirigimos de vuelta a la página de conexiones
        return redirect()->back()->with('success', '¡Conexión aceptada!');
    }

    /**
     * Rechaza una solicitud de conexión pendiente.
     */
    public function reject(Connection $connection)
    {
        // Medida de seguridad
        abort_if(Auth::id() !== $connection->receiver_id, 403, 'Acción no autorizada.');

        $connection->delete();

        return redirect()->back()->with('success', 'Solicitud rechazada.');
    }

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
        $gymPals = $currentUser->gym_pals->map(function ($gymPal) {
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