<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
    ];

    public function workouts()
    {
        return $this->hasMany(Workout::class);
    }

    public function workoutLogs()
    {
        return $this->hasMany(WorkoutLog::class);
    }
}
