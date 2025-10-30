<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Post;
use App\Models\User;

class LikesSeeder extends Seeder
{
    public function run()
    {
        $posts = Post::all();
        $users = User::where('id', '!=', 1)->get();

        foreach ($posts as $post) {
            // Elegimos un número aleatorio de usuarios (entre 0 y 10)
            $usersToLike = $users->random(rand(0, min(10, $users->count())));

            foreach ($usersToLike as $user) {
                // Evita duplicados usando firstOrCreate
                DB::table('post_like')->updateOrInsert([
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                ]);
            }
        }
    }
}