<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workout_id',
        'category_id',
        'workout_name',
        'exercises_data',
        'duration_minutes',
        'total_sets',
        'completed_sets',
        'notes',
    ];

    protected $casts = [
        'exercises_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workout()
    {
        return $this->belongsTo(Workout::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get personal records for a specific exercise
     */
    public static function getPersonalRecords($userId, $exerciseName)
    {
        $logs = self::where('user_id', $userId)->get();
        
        $maxWeight = 0;
        $maxReps = 0;
        $maxVolume = 0; // peso × reps
        
        foreach ($logs as $log) {
            foreach ($log->exercises_data as $exercise) {
                if (strtolower($exercise['name']) === strtolower($exerciseName)) {
                    foreach ($exercise['sets'] as $set) {
                        if ($set['completed'] ?? false) {
                            $weight = $set['weight'] ?? 0;
                            $reps = $set['reps'] ?? 0;
                            $volume = $weight * $reps;
                            
                            $maxWeight = max($maxWeight, $weight);
                            $maxReps = max($maxReps, $reps);
                            $maxVolume = max($maxVolume, $volume);
                        }
                    }
                }
            }
        }
        
        return [
            'max_weight' => $maxWeight,
            'max_reps' => $maxReps,
            'max_volume' => $maxVolume,
        ];
    }
}
