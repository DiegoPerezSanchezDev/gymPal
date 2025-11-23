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
        'sets_data',
        'rest_seconds',
        'notes',
        'order',
    ];

    protected $casts = [
        'sets_data' => 'array',
    ];

    // --- RELACIONES ---

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }
}
