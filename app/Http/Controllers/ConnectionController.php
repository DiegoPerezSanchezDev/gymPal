<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConnectionController extends Controller
{
    /**
     * Almacena una nueva solicitud de conexión.
     * Es llamado cuando un usuario hace clic en "Conectar".
     */
    public function store(Request $request, User $user)
    {
        // 1. OBTENEMOS LOS ACTORES
        // El usuario que está enviando la solicitud (el que está logueado)
        $sender = Auth::user();
        // El usuario que va a recibir la solicitud (Laravel lo obtiene automáticamente de la URL gracias al Route Model Binding)
        $receiver = $user;

        // 2. VALIDACIONES ESENCIALES
        // Evitar que un usuario se conecte consigo mismo
        if ($sender->id === $receiver->id) {
            return response()->json(['message' => 'No puedes conectarte contigo mismo.'], 422); // 422: Unprocessable Entity
        }

        // Evitar crear solicitudes duplicadas si ya existe una en cualquier dirección
        $existingConnection = Connection::where(function ($query) use ($sender, $receiver) {
            $query->where('sender_id', $sender->id)->where('receiver_id', $receiver->id);
        })->orWhere(function ($query) use ($sender, $receiver) {
            $query->where('sender_id', $receiver->id)->where('receiver_id', $sender->id);
        })->first();

        if ($existingConnection) {
            return response()->json(['message' => 'Ya existe una conexión o solicitud pendiente con este usuario.'], 409); // 409: Conflict
        }
        
        // 3. LA "HAPPY PATH": CREAR LA CONEXIÓN
        // Si todas las validaciones pasan, creamos la nueva solicitud
        $connection = Connection::create([
            'sender_id'   => $sender->id,
            'receiver_id' => $receiver->id,
            // El status 'pending' se aplica por defecto gracias a la migración
        ]);

        // 4. RESPUESTA DE ÉXITO
        // Devolvemos una respuesta JSON al frontend para confirmar que todo ha ido bien.
        // El código 201 significa "Created" (Creado).
        return response()->json([
            'message'    => 'Solicitud de conexión enviada con éxito.',
            'connection' => $connection 
        ], 201);
    }

    // Aquí irán los otros métodos: accept, reject, destroy...
}