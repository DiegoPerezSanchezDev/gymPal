<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class FeedController extends Controller
{
    public function index()
    {
        $simulatedAuthData = [
            'user' => (object) [
                'id' => 999,
                'name' => 'Usuario de Prueba',
                'email' => 'test@gympal.com',
            ]
        ];
        /* if (auth()->check()) { // Si hay un usuario real, sobreescribir
            $simulatedAuthData = ['user' => auth()->user()];
        } */
    
    
        return Inertia::render('Feed', [
            /* 'posts' => $samplePosts, */
            'title' => 'GymPal Feed',
            'auth' => $simulatedAuthData, // Pasas 'auth' directamente
            'isLoginPage' => false,
            'isRegisterPage' => false,
        ]);
    }
}
