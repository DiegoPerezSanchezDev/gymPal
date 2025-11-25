<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log; // Para logging
use Illuminate\Support\Facades\Storage; // Para manejo de archivos
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

        // --- MANEJO DE FOTO DE PERFIL ---
        // Eliminar foto si se solicitó
        if (!empty($data['remove_profile_picture']) && $user->profile_picture_url) {
            // Eliminar archivo físico si existe
            if (Storage::disk('public')->exists($user->profile_picture_url)) {
                Storage::disk('public')->delete($user->profile_picture_url);
            }
            $user->profile_picture_url = null;
        }

        // Subir nueva foto si se proporcionó
        if ($request->hasFile('profile_picture')) {
            // Eliminar foto anterior si existe
            if ($user->profile_picture_url && Storage::disk('public')->exists($user->profile_picture_url)) {
                Storage::disk('public')->delete($user->profile_picture_url);
            }

            // Guardar nueva foto
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $user->profile_picture_url = $path;
        }

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
        
        // Obtener conexiones manualmente para incluir el ID de la conexión
        $sent = Connection::where('sender_id', $user->id)
            ->where('status', 'accepted')
            ->with('receiver:id,name,username,profile_picture_url')
            ->get()
            ->map(function ($conn) {
                $u = $conn->receiver;
                $u->pivot = ['id' => $conn->id];
                return $u;
            });

        $received = Connection::where('receiver_id', $user->id)
            ->where('status', 'accepted')
            ->with('sender:id,name,username,profile_picture_url')
            ->get()
            ->map(function ($conn) {
                $u = $conn->sender;
                $u->pivot = ['id' => $conn->id];
                return $u;
            });

        $connections = $sent->merge($received);
        $connections_count = $connections->count();

        // Cargar posts si es el propio perfil o si están conectados
        $posts = [];
        if (($currentUser && $currentUser->id === $user->id) || $connectionStatus === 'accepted') {
            $posts = $user->posts()
                ->with([
                    'user:id,name,username,profile_picture_url',
                    'comments.user:id,name,username,profile_picture_url',
                    'likers' // Corregido: likes -> likers
                ])
                ->withCount(['comments', 'likers']) // Corregido: likes -> likers
                ->latest()
                ->get()
                ->map(function ($post) use ($currentUser) {
                    // Corregido: likes -> likers. likers son Usuarios, así que buscamos por id.
                    $post->is_liked = $currentUser ? $post->likers->contains('id', $currentUser->id) : false;
                    
                    // Optimización para likes: obtener los últimos 3 para mostrar avatares
                    // Corregido: likes() -> likers()
                    $post->latest_likers = $post->likers()->latest('post_like.created_at')->take(3)->get();
                    
                    // Optimización para comentarios: obtener los últimos 2
                    $post->latest_comments = $post->comments()->latest()->take(2)->with('user:id,name,username,profile_picture_url')->get()->reverse()->values();
                    return $post;
                });
        }

        // Cargar rutinas si es el propio perfil o si están conectados
        $workouts = [];
        if (($currentUser && $currentUser->id === $user->id) || $connectionStatus === 'accepted') {
            $query = $user->workouts()->with('exercises');
            
            // Si NO es tu propio perfil, solo mostrar públicas
            if (!$currentUser || $currentUser->id !== $user->id) {
                $query->where('is_public', true);
            }
            
            $workouts = $query->latest()->get();
        }

        return Inertia::render('Profile/ShowPublic', [
            'profileUser' => $user,
            'title' => 'Perfil de ' . $user->name,
            'isOwnProfile' => $currentUser ? $currentUser->id === $user->id : false,
            
            'connection_status' => $connectionStatus,
            'connection_id' => $connection ? $connection->id : null,
            'connections_count' => $connections_count,
            'connections_list' => $connections, // Pasar la lista de conexiones
            'posts' => $posts, // Pasar los posts
            'workouts' => $workouts, // Pasar las rutinas
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