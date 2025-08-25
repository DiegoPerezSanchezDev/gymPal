<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\DiscoverController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\ChatController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

// MODIFICAMOS ESTA SECCIÓN PARA EL FEED
Route::get('/feed', [FeedController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('feed.index');

    //El dashboard nos ira al index de feed
Route::get('/dashboard', function () {
    return redirect()->route('feed.index');
})->middleware(['auth', 'verified'])->name('dashboard');


//Rutas autentificadas de auth
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // CHAT
    Route::get('/chat', function() { // Placeholder
        return Inertia::render('Chat/Index', ['title' => 'Mis Chats']);
    })->name('chat.index');
    // Route::get('/chat/{user}', [ChatController::class, 'show'])->name('chat.show');

    // PROFILE (Breeze ya maneja profile.edit, update, destroy)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // POST, crear el post para subir a la página.
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store'); // Para guardar el post

    //DESCUBRIR usuarios gymPals
    Route::get('/discover', [DiscoverController::class, 'index'])->name('discover.index');

    // Ruta para ver perfiles públicos
    Route::get('/u/{user:username}', [ProfileController::class, 'showPublic'])->name('profile.show.public');

    //Ruta para seguir a otras personas
    Route::post('/users/{user}/toggle-follow', [FollowController::class, 'toggleFollow'])->name('profile.toggleFollow');

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{user:username}', [ChatController::class, 'show'])->name('chat.show');

    // RUTA PARA GUARDAR MENSAJES EN UNA CONVERSACIÓN
    Route::post('/chat/conversations/{conversation}/messages', [ChatController::class, 'storeMessage'])
        ->name('chat.messages.store');

        // En routes/web.php (dentro del middleware 'auth' si es necesario)
Route::get('/debug-user-lookup/{user:username}', function (\App\Models\User $user) {
    return response()->json([
        'message' => 'User lookup test',
        'user_exists' => $user->exists,
        'user_id' => $user->id,
        'user_username' => $user->username,
        'user_attributes' => $user->toArray(),
        'route_parameter_value' => request()->route('user')
    ]);
});

    // RUTAS PARA LIKES (ejemplo para PostCard.vue)
    // Route::post('/posts/{post}/like', [LikeController::class, 'toggle'])->name('posts.like');

});

require __DIR__.'/auth.php';