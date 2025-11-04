<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Connection;
use App\Models\Message;
use App\Models\Conversation;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el primer usuario (o crear uno si no existe)
        $user = User::first();
        
        if (!$user) {
            $this->command->warn('No hay usuarios en la base de datos. Ejecuta primero UserSeeder.');
            return;
        }

        // Obtener otros usuarios para crear notificaciones
        $otherUsers = User::where('id', '!=', $user->id)->take(5)->get();
        
        if ($otherUsers->isEmpty()) {
            $this->command->warn('Necesitas al menos 2 usuarios para crear notificaciones de prueba.');
            return;
        }

        $this->command->info("Creando notificaciones de prueba para: {$user->name}");

        // 1. Notificación de POST COMPARTIDO
        $post = Post::where('user_id', '!=', $user->id)->first();
        if ($post && $otherUsers->first()) {
            NotificationService::notifyPostShared($user, $post, $otherUsers->first());
            $this->command->info('✓ Notificación de post compartido creada');
        }

        // 2. Notificación de NUEVO MENSAJE
        $sender = $otherUsers->first();
        if ($sender) {
            // Crear una conversación de prueba si no existe
            $conversation = Conversation::whereHas('users', function($q) use ($user) {
                $q->where('users.id', $user->id);
            })->whereHas('users', function($q) use ($sender) {
                $q->where('users.id', $sender->id);
            })->first();

            if (!$conversation) {
                $conversation = Conversation::create(['last_message_at' => now()]);
                $conversation->users()->attach([$user->id, $sender->id]);
            }

            // Crear mensaje de prueba
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $sender->id,
                'body' => 'Hola! ¿Quieres entrenar juntos este fin de semana?',
                'type' => 'text',
            ]);

            NotificationService::notifyNewMessage($user, $message, $sender);
            $this->command->info('✓ Notificación de nuevo mensaje creada');
        }

        // 3. Notificación de CONEXIÓN ACEPTADA
        $accepter = $otherUsers->skip(1)->first();
        if ($accepter) {
            // Crear conexión de prueba
            $connection = Connection::firstOrCreate(
                [
                    'sender_id' => $user->id,
                    'receiver_id' => $accepter->id,
                ],
                ['status' => 'accepted']
            );
            
            if ($connection->status === 'accepted') {
                NotificationService::notifyConnectionAccepted($user, $connection, $accepter);
                $this->command->info('✓ Notificación de conexión aceptada creada');
            }
        }

        // 4. Notificación de POST CON LIKE
        $liker = $otherUsers->skip(2)->first();
        $userPost = Post::where('user_id', $user->id)->first();
        if ($userPost && $liker) {
            NotificationService::notifyPostLiked($user, $userPost, $liker);
            $this->command->info('✓ Notificación de post con like creada');
        }

        // 5. Notificación de COMENTARIO EN POST
        $commenter = $otherUsers->skip(3)->first();
        if ($userPost && $commenter) {
            // Crear comentario de prueba
            $comment = Comment::create([
                'user_id' => $commenter->id,
                'post_id' => $userPost->id,
                'body' => '¡Excelente entrenamiento! Me encantó tu rutina.',
            ]);

            NotificationService::notifyPostCommented($user, $userPost, $comment, $commenter);
            $this->command->info('✓ Notificación de comentario en post creada');
        }

        // Crear algunas notificaciones antiguas (leídas)
        $oldUser = $otherUsers->skip(4)->first();
        if ($oldUser && $userPost) {
            $notification = NotificationService::notifyPostLiked($user, $userPost, $oldUser);
            $notification->update(['read_at' => now()->subDays(2), 'created_at' => now()->subDays(2)]);
            $this->command->info('✓ Notificación antigua (leída) creada');
        }

        $this->command->info("\n✅ Se crearon notificaciones de prueba para {$user->name}");
        $this->command->info("Inicia sesión como este usuario para ver las notificaciones en el icono de campana del navbar.");
    }
}
