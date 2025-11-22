<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'display_name',
        'profile_picture_url',
        'bio',
        'location_city',
        'experience_level',
        'gender',
        'last_activity_at',
        'looking_for_interest_id',
        'latitude',
        'longitude',
        'availability_general',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'availability_general' => 'array',
        ];
    }

    // --- RELACIONES ESENCIALES DEL PERFIL ---

    public function lookingForInterest(): BelongsTo
    {
        return $this->belongsTo(FitnessInterest::class, 'looking_for_interest_id');
    }

    public function fitnessInterests(): BelongsToMany
    {
        return $this->belongsToMany(FitnessInterest::class, 'fitness_interest_user', 'user_id', 'fitness_interest_id');
    }

    // --- RELACIONES DE CONTENIDO ---

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->orderBy('created_at', 'desc');
    }

    public function likedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_like', 'user_id', 'post_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // --- RELACIONES DE CONEXIONES ---

    public function sentConnections(): HasMany
    {
        return $this->hasMany(Connection::class, 'sender_id');
    }

    public function receivedConnections(): HasMany
    {
        return $this->hasMany(Connection::class, 'receiver_id');
    }

    public function getGymPalsAttribute()
    {
        $sentAndAccepted = Connection::where('sender_id', $this->id)
                                ->where('status', 'accepted')
                                ->with('receiver')
                                ->get()
                                ->pluck('receiver');

        $receivedAndAccepted = Connection::where('receiver_id', $this->id)
                                    ->where('status', 'accepted')
                                    ->with('sender')
                                    ->get()
                                    ->pluck('sender');

        return $sentAndAccepted->merge($receivedAndAccepted);
    }

    // --- RELACIONES PARA CHAT ---

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class);
    }

    // --- RELACIONES PARA NOTIFICACIONES ---

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class)->latest();
    }

    public function unreadNotifications(): HasMany
    {
        return $this->hasMany(Notification::class)->whereNull('read_at')->latest();
    }

    public function readNotifications(): HasMany
    {
        return $this->hasMany(Notification::class)->whereNotNull('read_at')->latest();
    }

    // --- RELACIONES PARA RUTINAS ---

    public function workouts(): HasMany
    {
        return $this->hasMany(Workout::class);
    }

    public function savedWorkouts(): BelongsToMany
    {
        return $this->belongsToMany(Workout::class, 'saved_workouts')
            ->withTimestamps();
    }
}