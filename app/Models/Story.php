<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workout_log_id',
        'image_path',
        'content',
        'type',
        'metadata',
        'background_color',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $appends = ['is_viewed'];

    // Relación con el creador
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relación con el log de entrenamiento (Smart Stories)
    public function workoutLog(): BelongsTo
    {
        return $this->belongsTo(WorkoutLog::class);
    }

    // Quiénes la han visto
    public function viewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'story_views')
                    ->withPivot('viewed_at');
    }

    // Scope para traer solo historias activas
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    // Atributo calculado: ¿La he visto yo?
    public function getIsViewedAttribute(): bool
    {
        if (!Auth::check()) return false;
        
        // Si la relación ya está cargada (eager loading), usamos la colección
        // Nota: esto requiere cargar 'viewers' filtrando por Auth::id(), o usar exists()
        // Para simplificar en listas grandes, lo haremos eficiente en el Controller, 
        // pero aquí dejamos un fallback seguro.
        if ($this->relationLoaded('viewers')) {
            return $this->viewers->contains('id', Auth::id());
        }
        
        return $this->viewers()->where('user_id', Auth::id())->exists();
    }
}
