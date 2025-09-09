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
use Illuminate\Validation\Rule; 

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
        $user->fitnessInterests()->sync($validatedData['interests'] ?? []);

        return Redirect::route('profile.show.public', ['user' => $user->username])
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
       // Usamos loadMissing para cargar relaciones solo si no han sido cargadas ya.
        $user->loadMissing(['fitnessInterests', 'posts', 'followers', 'following']);

        $authenticatedUser = Auth::user();

        return Inertia::render('Profile/ShowPublic', [
            'title' => 'Perfil de ' . ($user->display_name ?: $user->name),
            'profileUser' => [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'display_name' => $user->display_name,
            'bio' => $user->bio,
            'profile_picture_url' => $user->profile_picture_url,
            'location_city' => $user->location_city,
            'availability_general' => $user->availability_general, // Laravel ya lo convierte a array
            'experience_level' => $user->experience_level,
            'created_at' => $user->created_at,
            'posts_count' => $user->posts->count(),
            'followers_count' => $user->followers->count(),
            'following_count' => $user->following->count(),
               'fitness_interests' => $user->fitnessInterests->pluck('name'), // Forma limpia de obtener solo los nombres
            ],
            'posts' => $user->posts()->paginate(9),
            'isFollowing' => $authenticatedUser ? $authenticatedUser->isFollowing($user) : false,
            'isOwnProfile' => $authenticatedUser ? $authenticatedUser->id === $user->id : false,
        ]);
    }

    public function updateLookingForInterest(Request $request): \Illuminate\Http\JsonResponse
    {
        //Validamos los datos que nos llegan.
        $validated = $request->validate([
            'interest_id' => ['required', 'integer', Rule::exists('fitness_interests', 'id')],
        ]);

        //Obtenemos el usuario autenticado.
        $user = $request->user();

        //Actualizamos el campo específico.
        $user->looking_for_interest_id = $validated['interest_id'];

        //Guardamos los cambios en la base de datos.
        $user->save();

        //Devolvemos una respuesta JSON para confirmar que todo ha ido bien.
        return response()->json(['message' => 'Interest updated successfully.']);
    }


}