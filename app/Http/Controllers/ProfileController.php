<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log; // Para logging
use Inertia\Inertia;
use App\Models\User;
// use Inertia\Response; // No es necesario si usas el type hint \Inertia\Response

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): \Inertia\Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'title' => 'Editar Perfil',
            'isLoginPage' => false, // Estas props pueden ser útiles para el layout
            'isRegisterPage' => false,
            'user' => $request->user()->load('fitnessInterests'), 
            'interests' => \App\Models\FitnessInterest::all(['id', 'name']),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        Log::info('[PERFIL UPDATE] Datos VALIDADOS:', $data);

        // Actualizar campos simples
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Guardar disponibilidad (array/JSON)
        if (isset($data['availability_general'])) {
            $user->availability_general = $data['availability_general'];
        }

        $user->save();

        // Guardar intereses deportivos (relación muchos a muchos)
        if (isset($data['interests'])) {
            $user->fitnessInterests()->sync($data['interests']);
        }else {
            // Si no se envía ningún interés, los desvinculamos todos
            $user->fitnessInterests()->detach();
        }

        return Redirect::route('profile.show.public', ['user' => $request->user()->username])
            ->with('success_toast', 'Perfil actualizado correctamente.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Display the specified user's public profile.
     */
    public function showPublic(User $user): \Inertia\Response // $user es inyectado por Route Model Binding
    {
        // Cargar posts paginados
        $posts = $user->posts()->paginate(9);

        $isFollowing = false;
        $isOwnProfile = false;
        $followersCount = 0;
        $followingCount = 0;

        // Cargar la relación fitnessInterests para acceder a ella
        // Si 'fitnessInterests' es un atributo JSON en el modelo User, no necesitas load().
        // Si es una relación BelongsToMany, sí.
        if (method_exists($user, 'fitnessInterests')) {
            $user->load('fitnessInterests');
            $fitnessInterestsData = $user->fitnessInterests ? $user->fitnessInterests->pluck('name')->all() : [];
        } else {
            // Si fitness_interests es un atributo (ej: JSON casteado a array)
            $fitnessInterestsData = $user->fitness_interests ?? [];
        }


        if (Auth::check()) {
            $authenticatedUser = Auth::user();
            $isOwnProfile = $authenticatedUser->id === $user->id;

            if (!$isOwnProfile) {
                if (method_exists($authenticatedUser, 'isFollowing')) {
                    $isFollowing = $authenticatedUser->isFollowing($user);
                } else {
                    Log::warning('ProfileController@showPublic: El método isFollowing() no existe en el modelo User del usuario autenticado.');
                }
            }
        }

        // Contadores de seguidores/seguidos (solo si los métodos existen)
        if (method_exists($user, 'followers')) {
            $followersCount = $user->followers()->count();
        }
        if (method_exists($user, 'following')) {
            $followingCount = $user->following()->count();
        }

        // Preparar los datos del perfil del usuario a mostrar
        $profileUserData = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'display_name' => $user->display_name,
            'bio' => $user->bio,
            'profile_picture_url' => $user->profile_picture_url, // Considera un accesor para URL completa o placeholder
            'location_city' => $user->location_city,
            'availability_general' => $user->availability_general,
            'experience_level' => $user->experience_level,
            'created_at' => $user->created_at,
            'posts_count' => $user->posts()->count(),
            'followers_count' => $followersCount,
            'following_count' => $followingCount,
            'fitness_interests' => $fitnessInterestsData,
        ];

        return Inertia::render('Profile/ShowPublic', [
            'title' => 'Perfil de ' . ($profileUserData['display_name'] ?: $profileUserData['name']),
            'profileUser' => $profileUserData,
            'posts' => $posts,
            'isFollowing' => $isFollowing,
            'isOwnProfile' => $isOwnProfile,
            'isLoginPage' => false,
            'isRegisterPage' => false,
        ]);
    }
}