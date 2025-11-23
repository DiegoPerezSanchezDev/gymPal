<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'image_path',
        'likes_count',
        'workout_log_id'
    ];

    protected $appends = ['is_liked'];

    // --- RELACIONES ---

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workoutLog()
    {
        return $this->belongsTo(WorkoutLog::class);
    }

    public function likers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_like', 'post_id', 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function latestComments()
    {
        return $this->hasMany(Comment::class)->latest('created_at')->take(2);
    }


    public function getLikesCountAttribute(): int
    {
        // Si viene de withCount('likers as likes_count'), úsalo; si no, fallback
        return $this->attributes['likes_count']
            ?? $this->attributes['likers_count']
            ?? 0;
    }

    public function getIsLikedAttribute(): bool
    {
        if (!Auth::check()) {
            return false;
        }
        
        // Si ya tenemos la relación cargada, usarla; si no, hacer consulta
        if ($this->relationLoaded('likers')) {
            return $this->likers->contains(Auth::id());
        }
        return $this->likers()->where('user_id', Auth::id())->exists();
    }

    //Sacamos los likes de los últimos que le dieron
    public function latestLikers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_like', 'post_id', 'user_id')
                    ->latest('post_like.created_at') // Ordena por la fecha en la tabla pivote
                    ->limit(3);
    }

    //Reportar
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

}
