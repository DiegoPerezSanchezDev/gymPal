<?php

namespace App\Http\Controllers;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request, Post $post) {
        $request->validate(['reason' => 'required|string|max:255']);
        $post->reports()->firstOrCreate(['user_id' => Auth::id()], ['reason' => $request->reason]);
        return back()->with('success_toast', 'Publicación denunciada. Gracias.');
    }
}
