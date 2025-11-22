<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'workout_id',
        'exercise_name',
        'sets',
        'reps',
        'weight_kg',
        'rest_seconds',
        'notes',
        'order',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:2',
    ];

    // --- RELACIONES ---

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }
}
