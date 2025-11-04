<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

class CommentController extends Controller
{
    public function store(Request $request)
{
    $validated = $request->validate([
        'post_id' => 'required|exists:posts,id',
        'body' => 'required|string',
    ]);

    $post = Post::with('user')->findOrFail($validated['post_id']);
    $currentUser = Auth::user();
    
    $comment = Comment::create([
        'user_id' => $currentUser->id,
        'post_id' => $validated['post_id'],
        'body' => $validated['body'],
    ]);
    $comment->load('user');

    $comments_count = $post->comments()->count();
    
    // Crear notificación solo si el usuario que comenta no es el dueño del post
    if ($post->user_id !== $currentUser->id) {
        NotificationService::notifyPostCommented($post->user, $post, $comment, $currentUser);
    }

    return response()->json([
        ...$comment->toArray(),
        'comments_count' => $comments_count
    ], 201);
}
}