<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gym extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'type',
        'address',
        'city',
        'website',
        'rating',
        'users_count',
        'meta_data',
        'external_id'
    ];

    protected $casts = [
        'meta_data' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'users_count' => 'integer',
        'rating' => 'float',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
