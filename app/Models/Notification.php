<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'notifiable_id',
        'notifiable_type',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    // Constantes para tipos de notificación
    const TYPE_POST_SHARED = 'post_shared';
    const TYPE_NEW_MESSAGE = 'new_message';
    const TYPE_CONNECTION_REQUEST = 'connection_request';
    const TYPE_CONNECTION_ACCEPTED = 'connection_accepted';
    const TYPE_POST_LIKED = 'post_liked';
    const TYPE_POST_COMMENTED = 'post_commented';

    /**
     * Usuario que recibe la notificación
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación polimórfica con el modelo relacionado (Post, Message, Connection, etc.)
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Marcar notificación como leída
     */
    public function markAsRead(): bool
    {
        return $this->update(['read_at' => now()]);
    }

    /**
     * Verificar si está leída
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }
}
