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
        'banner_picture_url',
        'banner_color',
        'bio',
        'location_city',
        'experience_level',
        'gender',
        'last_activity_at',
        'looking_for_interest_id',
        'latitude',
        'longitude',
        'gym_id',
        'availability_general',
        'onboarding_completed',
        'onboarding_skipped',
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
            'onboarding_completed' => 'boolean',
            'onboarding_skipped' => 'boolean',
        ];
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class)->withTimestamps();
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

    public function savedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'saved_posts')
            ->withTimestamps();
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

    /**
     * Solicitudes pendientes que he enviado
     */
    public function getPendingSentAttribute()
    {
        return Connection::where('sender_id', $this->id)
            ->where('status', 'pending')
            ->with('receiver')
            ->get()
            ->pluck('receiver');
    }

    /**
     * Solicitudes pendientes que he recibido
     */
    public function getPendingReceivedAttribute()
    {
        return Connection::where('receiver_id', $this->id)
            ->where('status', 'pending')
            ->with('sender')
            ->get()
            ->pluck('sender');
    }

    /**
     * GymPals - Conexiones aceptadas (mutuamente conectados)
     */
    public function getGymPalsAttribute()
    {
        // En un sistema de "Solicitud", una conexión accepted significa amistad bidireccional si la lógica es "A pide a B, B acepta".
        // Si la conexión es única (A->B accepted), son amigos.
        // Si el sistema anterior usaba registros dobles, habrá que ver. 
        // Basándome en la migración original: "$table->unique(['sender_id', 'receiver_id']);".
        // Asumimos que A->B accepted es suficiente para que sean amigos, o...
        // Espera, el código original de User.php antes de mi cambio usaba sentAndAccepted MERGE receivedAndAccepted.
        // Restauro esa lógica exacta.

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

        return $sentAndAccepted->merge($receivedAndAccepted)->unique('id')->values();
    }

    /**
     * Verificar si tengo una conexión pendiente con un usuario
     */
    public function hasPendingConnectionWith($userId)
    {
        return Connection::where(function($query) use ($userId) {
            $query->where('sender_id', $this->id)
                  ->where('receiver_id', $userId);
        })->orWhere(function($query) use ($userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $this->id);
        })->where('status', 'pending')->exists();
    }

    /**
     * Verificar si somos GymPals (conexión aceptada)
     */
    public function isGymPalWith($userId)
    {
        return Connection::where(function($query) use ($userId) {
            $query->where('sender_id', $this->id)
                  ->where('receiver_id', $userId);
        })->orWhere(function($query) use ($userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $this->id);
        })->where('status', 'accepted')->exists();
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

    public function workoutLogs(): HasMany
    {
        return $this->hasMany(WorkoutLog::class);
    }

    // --- RELACIONES PARA STORIES ---
    public function stories(): HasMany
    {
        return $this->hasMany(Story::class)->orderBy('created_at', 'asc');
    }

    public function activeStories(): HasMany
    {
        return $this->hasMany(Story::class)
            ->where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at', 'asc');
    }

    // --- RELACIONES PARA GYMS ---
    public function gyms(): BelongsToMany
    {
        return $this->belongsToMany(Gym::class, 'gym_user')->withTimestamps();
    }
}