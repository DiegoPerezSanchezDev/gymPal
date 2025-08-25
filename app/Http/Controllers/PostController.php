<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Post; 
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Para manejar la subida de archivos

class PostController extends Controller
{
    public function create()
    {
        return Inertia::render('Posts/Create', [
            'title' => 'Crear Nueva Publicación',
            'isLoginPage' => false,
            'isRegisterPage' => false,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'content' => 'nullable|string|max:1000', // Contenido puede ser nulo si hay imagen
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Imagen opcional, tipos y tamaño
        ]);

        // Asegurarse de que al menos uno de los dos esté presente
        if (empty($validatedData['content']) && !$request->hasFile('image')) {
            return back()->withErrors(['content' => 'La publicación debe tener contenido o una imagen.']);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            // Guardar la imagen en public/storage/posts (crea el enlace simbólico con `php artisan storage:link`)
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        

        $user = Auth::user();

        if (!$user instanceof \App\Models\User) {
            dd('Auth::user() no es una instancia de App\Models\User. Es: ' . get_class($user));
        }
        if (!method_exists($user, 'posts')) {
            dd('El método posts() NO existe en la instancia de ' . get_class($user));
        }
        
        // Crear el post en la base de datos
        $user->posts()->create([ // Asume que el modelo User tiene una relación 'post()'
            'content' => $validatedData['content'] ?? null,
            'image_path' => $imagePath, // Guarda la ruta de la imagen
        ]);

        // Redirigir de vuelta al feed con un mensaje de éxito (opcional)
        // Inertia.js maneja la redirección si haces un return redirect()->...
        // return redirect()->route('feed.index')->with('success', '¡Publicación creada!');

        return redirect()->route('feed.index'); // O a donde quieras ir después de crear
                                             // Si quieres quedarte en la misma página y mostrar
                                             // un mensaje de éxito, podrías hacer:
                                             // return back()->with('success_toast', '¡Publicación creada!');
}

}
