<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Message extends Model
{
    use HasFactory;
    protected $fillable = ['conversation_id', 'user_id', 'body', 'image_url', 'read_at', 'type', 'metadata'];
    protected $casts = [
        'read_at' => 'datetime',
        'metadata' => 'array', // Cast automático de JSON a array
    ];
    
    // Constantes para tipos de mensaje
    const TYPE_TEXT = 'text';
    const TYPE_SHARED_POST = 'shared_post';
    const TYPE_SHARED_WORKOUT = 'shared_workout';
    
    public function conversation(): BelongsTo { 
        return $this->belongsTo(Conversation::class); 
    }
    
    public function user(): BelongsTo { 
        return $this->belongsTo(User::class); // El remitente
    }
    
    // Helper para obtener el post compartido si existe
    public function getSharedPostAttribute()
    {
        if ($this->type === self::TYPE_SHARED_POST && isset($this->metadata['post_id'])) {
            return Post::with('user')->find($this->metadata['post_id']);
        }
        return null;
    }
    
    // Helper para obtener la rutina compartida si existe
    public function getSharedWorkoutAttribute()
    {
        if ($this->type === self::TYPE_SHARED_WORKOUT && isset($this->metadata['workout_id'])) {
            return Workout::with('user')->find($this->metadata['workout_id']);
        }
        return null;
    }
    
    // Helper para verificar si es un post compartido
    public function isSharedPost(): bool
    {
        return $this->type === self::TYPE_SHARED_POST;
    }
    
    // Helper para verificar si es una rutina compartida
    public function isSharedWorkout(): bool
    {
        return $this->type === self::TYPE_SHARED_WORKOUT;
    }
}