<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne; // Para latestMessage

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        // Podrías tener campos como 'title' si son chats grupales, etc.
        // Para chats 1-a-1, a menudo no se necesita mucho aquí.
        'last_message_at', // Útil para ordenar
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    protected $touches = ['latestMessage'];

    public function users()
{
    return $this->belongsToMany(User::class);
}

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'desc');
    }

    // Relación para obtener el último mensaje de la conversación
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latest('created_at');
    }
}