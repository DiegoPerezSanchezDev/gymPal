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
use App\Models\Connection;
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
        $user->fitnessInterests()->sync($data['interests'] ?? []);

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
    public function showPublic(User $user): \Inertia\Response
    {
        $currentUser = Auth::user();

        $connectionStatus = 'none';
        $connection = null;

        if ($currentUser && $currentUser->id !== $user->id) {
            $connection = Connection::where(function ($query) use ($currentUser, $user) {
                $query->where('sender_id', $currentUser->id)->where('receiver_id', $user->id);
            })->orWhere(function ($query) use ($currentUser, $user) {
                $query->where('sender_id', $user->id)->where('receiver_id', $currentUser->id);
            })->first();

            if ($connection) {
                if ($connection->status === 'pending') {
                    $connectionStatus = $connection->sender_id === $currentUser->id ? 'sent' : 'received';
                } else {
                    $connectionStatus = $connection->status; 
                }
            }
        }

        $user->load('fitnessInterests');

        $user->loadCount('posts');
        
        $connections_count = $user->gym_pals->count();

        return Inertia::render('Profile/ShowPublic', [
            'profileUser' => $user,
            'title' => 'Perfil de ' . $user->name,
            'isOwnProfile' => $currentUser ? $currentUser->id === $user->id : false,
            
            'connection_status' => $connectionStatus,
            'connection_id' => $connection ? $connection->id : null,
            'connections_count' => $connections_count,
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