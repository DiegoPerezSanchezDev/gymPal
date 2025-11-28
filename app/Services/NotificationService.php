<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    /**
     * Crear una notificación para un usuario
     */
    public static function create(
        User $user,
        string $type,
        string $title,
        string $message,
        ?Model $notifiable = null,
        ?array $data = null
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'notifiable_id' => $notifiable?->id,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'data' => $data,
        ]);
    }

    /**
     * Notificar cuando se comparte un post
     */
    public static function notifyPostShared(User $recipient, $post, User $sharer): Notification
    {
        return self::create(
            $recipient,
            Notification::TYPE_POST_SHARED,
            'Post compartido',
            "{$sharer->name} compartió una publicación contigo",
            $post,
            [
                'post_id' => $post->id,
                'post_author_name' => $post->user->name,
                'post_content_preview' => $post->content ? substr($post->content, 0, 100) : null,
                'sharer_name' => $sharer->name,
                'sharer_id' => $sharer->id,
            ]
        );
    }

    /**
     * Notificar nuevo mensaje (solo si el usuario no está en el chat)
     */
    public static function notifyNewMessage(User $recipient, $message, User $sender): Notification
    {
        return self::create(
            $recipient,
            Notification::TYPE_NEW_MESSAGE,
            'Nuevo mensaje',
            "{$sender->name} te envió un mensaje",
            $message,
            [
                'message_id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'sender_name' => $sender->name,
                'sender_username' => $sender->username,
                'sender_id' => $sender->id,
                'message_preview' => substr($message->body, 0, 100),
            ]
        );
    }

    /**
     * Notificar nueva solicitud de conexión
     */
    public static function notifyConnectionRequest(User $receiver, $connection, User $sender): Notification
    {
        return self::create(
            $receiver,
            Notification::TYPE_CONNECTION_REQUEST,
            'Nueva solicitud de conexión',
            "{$sender->name} quiere conectar contigo",
            $connection,
            [
                'connection_id' => $connection->id,
                'sender_name' => $sender->name,
                'sender_username' => $sender->username,
                'sender_id' => $sender->id,
            ]
        );
    }

    /**
     * Notificar conexión aceptada
     */
    public static function notifyConnectionAccepted(User $user, $connection, User $accepter): Notification
    {
        return self::create(
            $user,
            Notification::TYPE_CONNECTION_ACCEPTED,
            'Conexión aceptada',
            "{$accepter->name} aceptó tu solicitud de conexión",
            $connection,
            [
                'connection_id' => $connection->id,
                'accepter_name' => $accepter->name,
                'accepter_username' => $accepter->username,
                'accepter_id' => $accepter->id,
            ]
        );
    }

    /**
     * Notificar like en un post
     */
    public static function notifyPostLiked(User $postOwner, $post, User $liker): Notification
    {
        return self::create(
            $postOwner,
            Notification::TYPE_POST_LIKED,
            'Like en tu post',
            "A {$liker->name} le gustó tu publicación",
            $post,
            [
                'post_id' => $post->id,
                'liker_name' => $liker->name,
                'liker_id' => $liker->id,
            ]
        );
    }

    /**
     * Notificar comentario en un post
     */
    public static function notifyPostCommented(User $postOwner, $post, $comment, User $commenter): Notification
    {
        return self::create(
            $postOwner,
            Notification::TYPE_POST_COMMENTED,
            'Comentario en tu post',
            "{$commenter->name} comentó en tu publicación",
            $post,
            [
                'post_id' => $post->id,
                'comment_id' => $comment->id,
                'commenter_name' => $commenter->name,
                'commenter_id' => $commenter->id,
                'comment_preview' => substr($comment->body, 0, 100),
            ]
        );
    }
}

