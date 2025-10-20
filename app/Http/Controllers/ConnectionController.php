<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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

        $existingConnection = Connection::where(function ($query) use ($sender, $receiver) {
            $query->where('sender_id', $sender->id)->where('receiver_id', $receiver->id);
        })->orWhere(function ($query) use ($sender, $receiver) {
            $query->where('sender_id', $receiver->id)->where('receiver_id', $sender->id);
        })->first();

        if ($existingConnection) {
            return response()->json(['message' => 'Ya existe una conexión o solicitud pendiente.'], 409);
        }
        
        $connection = Connection::create([
            'sender_id'   => $sender->id,
            'receiver_id' => $receiver->id,
        ]);

        return response()->json(['message' => 'Solicitud de conexión enviada.'], 201);
    }

    /**
     * Muestra la página "Mis Conexiones".
     */
    public function index()
    {
        $currentUser = User::find(Auth::id());
        $currentUser->load('fitnessInterests');
        $currentUserInterestIds = $currentUser->fitnessInterests->pluck('id');

        //Obtener solicitudes PENDIENTES que he recibido
        $pendingRequests = Connection::where('receiver_id', $currentUser->id)
                                    ->where('status', 'pending')
                                    ->with('sender.fitnessInterests')
                                    ->orderBy('created_at', 'desc')
                                    ->get()
                                    ->map(function ($request) use ($currentUserInterestIds) {
                                        $commonInterests = $request->sender->fitnessInterests->whereIn('id', $currentUserInterestIds);
                                        $request->sender->common_interests_count = $commonInterests->count();
                                        $request->sender->common_interests_list = $commonInterests->pluck('name')->take(2);
                                        return $request;
                                    });

        //Obtener conexiones ACEPTADAS
        $acceptedConnections = Connection::where('status', 'accepted')
            ->where(function ($query) use ($currentUser) {
                $query->where('sender_id', $currentUser->id)
                    ->orWhere('receiver_id', $currentUser->id);
            })
            ->with(['sender', 'receiver'])
            ->get();

        //Transformar conexiones en una lista de amigos
        $gymPals = $acceptedConnections->map(function ($connection) use ($currentUser) {
            $friend = $connection->sender_id === $currentUser->id ? $connection->receiver : $connection->sender;
            // Añadimos el ID de la conexión para poder usarlo en el botón "Desconectar"
            $friend->connection_id = $connection->id; 
            return $friend;
        });

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

    // Aquí irán los otros métodos: destroy...
}