<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class FeedController extends Controller
{
    public function index()
    {
        // Aquí es donde harás la consulta para obtener los posts reales.
        // Por ahora, lo dejamos como un array vacío para que no falle.
        $posts = []; 
        
        return Inertia::render('Feed', [
            'posts' => $posts,
            'title' => 'Tu Feed',
        ]);
    }
}
