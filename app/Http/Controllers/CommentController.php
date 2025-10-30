<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'body' => 'required|string',
        ]);

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'post_id' => $validated['post_id'],
            'body' => $validated['body'],
        ]);

        // Cargar la información del usuario que creó el comentario ('user')
        $comment->load('user');

        return response()->json($comment, 201);
    }
}