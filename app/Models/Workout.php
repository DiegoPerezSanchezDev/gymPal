<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Workout extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'difficulty',
        'duration_minutes',
        'category',
        'is_public',
        'times_saved',
        'original_workout_id',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    // --- RELACIONES ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(WorkoutExercise::class)->orderBy('order');
    }

    // Usuarios que guardaron esta rutina
    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_workouts')
            ->withTimestamps();
    }

    // --- SCOPES ---

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByDifficulty($query, $level)
    {
        return $query->where('difficulty', $level);
    }

    // --- METHODS ---

    public function incrementSaveCount()
    {
        $this->increment('times_saved');
    }

    public function decrementSaveCount()
    {
        $this->decrement('times_saved');
    }
}
