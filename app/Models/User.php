<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail; // Descomenta si necesitas verificación de email
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// Podrías necesitar importar el modelo Post si no está en el mismo namespace y lo usas en otro lugar.
// use App\Models\Post;

class User extends Authenticatable // Implementa MustVerifyEmail si es necesario
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
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
        
        'gender',                   // Para el filtro por género
        'last_activity_at',         // Para el filtro "Más Activos"
        'looking_for_interest_id',  // Para el filtro "Buscando Compañero"
        'latitude',                 // Para geolocalización
        'longitude',                // Para geolocalización
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
            // Si 'availability_general' se guarda como JSON en la BD:
            'availability_general' => 'array',
        ];
    }

    public function lookingForInterest(): BelongsTo
    {
        return $this->belongsTo(FitnessInterest::class, 'looking_for_interest_id');
    }

    /* protected $appends = ['fitnessInterests']; */

    // --- RELACIONES PARA POSTS ---
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->orderBy('created_at', 'desc');
    }

    // --- RELACIONES PARA SEGUIMIENTO (FOLLOWERS/FOLLOWING) ---
    /**
     * Los usuarios que este usuario sigue (a quién sigo yo).
     */
    public function following(): BelongsToMany
    {
        // 'followers' es el nombre de la tabla pivote.
        // 'follower_id' es la clave foránea en la tabla pivote que referencia al usuario que está haciendo la acción de seguir.
        // 'following_id' es la clave foránea en la tabla pivote que referencia al usuario que está siendo seguido.
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'following_id')->withTimestamps();
    }

    /**
     * Los usuarios que siguen a este usuario (quiénes me siguen a mí).
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'following_id', 'follower_id')->withTimestamps();
    }

    /**
     * Método helper para verificar si el usuario actual sigue a otro usuario.
     */
    public function isFollowing(User $userToFollow): bool
    {
        if (!$this->relationLoaded('following')) {
            $this->load('following');
        }
        return $this->following()->where('users.id', $userToFollow->id)->exists();
    }

    // --- RELACIONES PARA CHAT ---
    /**
     * Las conversaciones en las que participa este usuario.
     */
    public function conversations()
{
    return $this->belongsToMany(Conversation::class);
}

public function fitnessInterests(): BelongsToMany
{
    return $this->belongsToMany(
        FitnessInterest::class,    // 1. Modelo con el que nos relacionamos
        'fitness_interest_user', // 2. Nombre de la TABLA PIVOTE
        'user_id',                 // 3. Nombre de la CLAVE FORÁNEA de ESTE modelo (User) en la tabla pivote
        'fitness_interest_id',   // 4. Nombre de la CLAVE FORÁNEA del OTRO modelo en la tabla pivote
        'id',                      // 5. Nombre de la CLAVE PRIMARIA de ESTE modelo (User)
        'id'                       // 6. Nombre de la CLAVE PRIMARIA del OTRO modelo (FitnessInterest)
    )->withTimestamps();
}

    // --- OTRAS RELACIONES POTENCIALES PARA GYMPAL (A FUTURO) ---


}