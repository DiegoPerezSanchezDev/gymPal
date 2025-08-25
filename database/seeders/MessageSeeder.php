<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Message; // Asegúrate de que el namespace sea correcto
use App\Models\Conversation;
use App\Models\User;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conversations = Conversation::with('users')->get(); // Cargar usuarios para saber quiénes participan

        if ($conversations->isEmpty()) {
            $this->command->info('No hay conversaciones para añadir mensajes.');
            return;
        }

        foreach ($conversations as $conversation) {
            if ($conversation->users->isEmpty()) {
                continue; // No se pueden enviar mensajes si no hay usuarios en la conversación
            }

            $participants = $conversation->users;
            $lastMessageTime = $conversation->last_message_at ?? now()->subHour(); // Usar el last_message_at o un tiempo base

            for ($i = 0; $i < rand(3, 10); $i++) { // Crear entre 3 y 10 mensajes por conversación
                $sender = $participants->random(); // Elegir un remitente aleatorio de los participantes
                $messageTime = now()->subMinutes(rand(1, 59 - $i)); // Mensajes escalonados en el tiempo

                // Asegurarse de que los mensajes sean cronológicos y actualicen last_message_at
                if ($messageTime > $lastMessageTime) {
                    $lastMessageTime = $messageTime;
                } else {
                    $messageTime = $lastMessageTime->addSeconds(rand(10, 60)); // Siguiente mensaje un poco después
                    $lastMessageTime = $messageTime;
                }


                Message::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $sender->id,
                    'body' => "Este es el mensaje de prueba número " . ($i + 1) . " de {$sender->name}.",
                    'created_at' => $messageTime,
                    'updated_at' => $messageTime,
                ]);
            }
            // Actualizar el last_message_at de la conversación al tiempo del último mensaje
            $conversation->update(['last_message_at' => $lastMessageTime]);
        }
        $this->command->info(Message::count() . ' mensajes creados.');
    }
}
