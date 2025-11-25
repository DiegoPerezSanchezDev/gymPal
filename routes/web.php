<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\DiscoverController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PostLikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConnectionController;
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
    // Ver el detalle de un post
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
    //Lista para ver quien le da like a un post
    Route::get('/posts/{post}/likes', [PostController::class, 'getLikers'])->name('posts.likes.index');

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

    //Ruta para actualizar dinámicamente el interés de búsqueda del usuario.
    Route::patch('/profile/looking-for', [ProfileController::class, 'updateLookingForInterest'])->name('profile.updateLookingFor');

    // Para ENVIAR una nueva solicitud de conexión a un usuario
    Route::post('/connections/{user}', [ConnectionController::class, 'store'])->name('connections.store');

    // Para VER la página principal de "Mis Conexiones"
    Route::get('/connections', [ConnectionController::class, 'index'])->name('connections.index');
    
    // Para ACEPTAR una solicitud que hemos recibido
    //Le pasamos el ID de la 'connection'
    Route::patch('/connections/{connection}/accept', [ConnectionController::class, 'accept'])->name('connections.accept');

    // Para RECHAZAR una solicitud que hemos recibido
    Route::patch('/connections/{connection}/reject', [ConnectionController::class, 'reject'])->name('connections.reject');

    // Para ELIMINAR una conexión que ya teníamos
    Route::delete('/connections/{connection}', [ConnectionController::class, 'destroy'])->name('connections.destroy');

    // Para dar like a un Post
    Route::post('/posts/{post}/like', [PostLikeController::class, 'toggleLike'])->name('posts.like.toggle');

    //Para ver una lista de los que dan likes
    Route::get('posts/{post}/likers', [PostController::class, 'getLikers'])->name('posts.likes.index');

    //Comentarios en los POST
    Route::post('/comments', [CommentController::class, 'store'])->middleware('auth:sanctum');

    //Eliminar el post, siendo el escritor de él
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    //Reportar el post de otra persona
    Route::post('/posts/{post}/report', [ReportController::class, 'store'])->name('posts.report');

    //Compartir un post con otro usuario
    Route::post('/posts/{post}/share', [PostController::class, 'share'])->name('posts.share');

    //Obtener conexiones del usuario (API)
    Route::get('/connections/gym-pals', [ConnectionController::class, 'getGymPals'])->name('connections.gym-pals');

    // Notificaciones
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/test', function() {
        return \Inertia\Inertia::render('Notifications/Test');
    })->name('notifications.test');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::delete('/notifications/delete-all', [NotificationController::class, 'deleteAll'])->name('notifications.delete-all');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Rutinas (Workouts)
    Route::get('/workouts', [\App\Http\Controllers\WorkoutController::class, 'index'])->name('workouts.index');
    Route::get('/workouts/create', [\App\Http\Controllers\WorkoutController::class, 'create'])->name('workouts.create');
    Route::post('/workouts', [\App\Http\Controllers\WorkoutController::class, 'store'])->name('workouts.store');
    Route::get('/workouts/saved', [\App\Http\Controllers\WorkoutController::class, 'saved'])->name('workouts.saved');
    Route::get('/workouts/my-workouts', [\App\Http\Controllers\WorkoutController::class, 'myWorkouts'])->name('workouts.my-workouts');
    Route::get('/workouts/{workout}', [\App\Http\Controllers\WorkoutController::class, 'show'])->name('workouts.show');
    Route::get('/workouts/{workout}/edit', [\App\Http\Controllers\WorkoutController::class, 'edit'])->name('workouts.edit');
    Route::put('/workouts/{workout}', [\App\Http\Controllers\WorkoutController::class, 'update'])->name('workouts.update');
    Route::delete('/workouts/{workout}', [\App\Http\Controllers\WorkoutController::class, 'destroy'])->name('workouts.destroy');
    Route::post('/workouts/{workout}/toggle-save', [\App\Http\Controllers\WorkoutController::class, 'toggleSave'])->name('workouts.toggle-save');
    Route::post('/workouts/{workout}/duplicate', [\App\Http\Controllers\WorkoutController::class, 'duplicate'])->name('workouts.duplicate');
    Route::get('/workouts/{workout}/live', [\App\Http\Controllers\WorkoutController::class, 'live'])->name('workouts.live');

    // Workout Logs (Historial de entrenamientos)
    Route::get('/workout-logs', [\App\Http\Controllers\WorkoutLogController::class, 'index'])->name('workout-logs.index');
    Route::get('/workout-logs/calendar', [\App\Http\Controllers\WorkoutLogController::class, 'calendar'])->name('workout-logs.calendar');
    Route::post('/workout-logs', [\App\Http\Controllers\WorkoutLogController::class, 'store'])->name('workout-logs.store');
    Route::get('/workout-logs/{workoutLog}', [\App\Http\Controllers\WorkoutLogController::class, 'show'])->name('workout-logs.show');
    Route::delete('/workout-logs/{workoutLog}', [\App\Http\Controllers\WorkoutLogController::class, 'destroy'])->name('workout-logs.destroy');
    Route::get('/personal-records', [\App\Http\Controllers\WorkoutLogController::class, 'personalRecords'])->name('personal-records');

});

require __DIR__.'/auth.php';