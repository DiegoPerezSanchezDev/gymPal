<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB; // Para inserciones directas si es necesario

class FollowerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->count() < 2) {
            $this->command->info('No hay suficientes usuarios para crear relaciones de seguimiento.');
            return;
        }

        // Que el usuario 'diegito' siga a algunos
        $diegito = User::where('email', 'diegito866@gmail.com')->first();
        $otrosUsuarios = User::where('email', '!=', 'diegito866@gmail.com')->take(2)->get();

        if ($diegito && $otrosUsuarios->count() > 0) {
            foreach ($otrosUsuarios as $otroUsuario) {
                // Usamos insertOrIgnore para evitar errores si la relación ya existe
                // (por si corres el seeder varias veces sin --fresh)
                DB::table('followers')->insertOrIgnore([
                    'follower_id' => $diegito->id,
                    'following_id' => $otroUsuario->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Que algunos usuarios sigan a 'diegito'
        $seguidoresDeDiegito = User::where('email', '!=', 'diegito866@gmail.com')->skip(2)->take(2)->get();
        if ($diegito && $seguidoresDeDiegito->count() > 0) {
            foreach ($seguidoresDeDiegito as $seguidor) {
                DB::table('followers')->insertOrIgnore([
                    'follower_id' => $seguidor->id,
                    'following_id' => $diegito->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
