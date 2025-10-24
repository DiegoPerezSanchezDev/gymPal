<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'image_path',
        'likes_count'
    ];

    protected $appends = ['is_liked'];

    // --- RELACIONES ---

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_like', 'post_id', 'user_id');
    }


    public function getLikesCountAttribute(): int
    {
        // Usar directamente la columna de la base de datos (más eficiente)
        return $this->attributes['likes_count'] ?? 0;
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

}
