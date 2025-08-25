<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Log; // Importante para el logging

class ConversationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Iniciando ConversationSeeder...');

        if (User::count() < 2) {
            $this->command->error('No hay suficientes usuarios (se necesitan al menos 2) para crear conversaciones. Seeder omitido.');
            Log::warning('ConversationSeeder: No hay suficientes usuarios.');
            return;
        }

        $diegito = User::where('email', 'diegito866@gmail.com')->first();
        Log::info('ConversationSeeder: Usuario Diegito: ' . ($diegito ? $diegito->id . ' - ' . $diegito->name : 'No encontrado'));

        $convo1Participants = collect();

        if ($diegito) {
            $otroUsuarioParaDiegito = User::where('id', '!=', $diegito->id)
                ->inRandomOrder()
                ->first();
            Log::info('ConversationSeeder: Otro usuario para Diegito: ' . ($otroUsuarioParaDiegito ? $otroUsuarioParaDiegito->id . ' - ' . $otroUsuarioParaDiegito->name : 'No encontrado'));

            if ($otroUsuarioParaDiegito) {
                $convo1 = Conversation::create(['last_message_at' => now()->subMinutes(rand(5, 60))]);
                Log::info("ConversationSeeder: Convo1 creada con ID: {$convo1->id}");

                $idsToAttachConvo1 = [$diegito->id, $otroUsuarioParaDiegito->id];
                Log::info("ConversationSeeder: IDs para adjuntar a Convo1 (ID {$convo1->id}): " . print_r($idsToAttachConvo1, true));

                if (in_array(null, $idsToAttachConvo1, true)) {
                    Log::error("ConversationSeeder: ¡NULO DETECTADO para Convo1 (ID {$convo1->id})! IDs: " . print_r($idsToAttachConvo1, true));
                    dd("NULO DETECTADO para Convo1", $idsToAttachConvo1, $diegito, $otroUsuarioParaDiegito); // DETENER EJECUCIÓN
                }
                $convo1->users()->attach($idsToAttachConvo1);
                $this->command->info("Conversación 1 creada (ID {$convo1->id}) entre: {$diegito->name} y {$otroUsuarioParaDiegito->name}.");
                $convo1Participants->push($diegito->id, $otroUsuarioParaDiegito->id);
            } else {
                $this->command->warn("No se encontró otro usuario distinto a {$diegito->name} para crear la Conversación 1.");
                Log::warning("ConversationSeeder: No se encontró segundo participante para Convo1 con Diegito.");
            }
        } else {
            $this->command->warn("Conversación 1 (con Diegito) omitida porque 'diegito866@gmail.com' no fue encontrado.");
            Log::warning("ConversationSeeder: Diegito no encontrado, Convo1 omitida.");
        }

        $idsToExcludeForConvo2 = $convo1Participants->unique()->toArray();
        Log::info("ConversationSeeder: IDs a excluir para Convo2: " . print_r($idsToExcludeForConvo2, true));

        $usersForConvo2 = User::whereNotIn('id', $idsToExcludeForConvo2)
            ->inRandomOrder()
            ->take(2)
            ->get();
        Log::info("ConversationSeeder: Usuarios encontrados para Convo2 (count {$usersForConvo2->count()}): " . print_r($usersForConvo2->pluck('id')->toArray(), true));

        if ($usersForConvo2->count() == 2) {
            $convo2 = Conversation::create(['last_message_at' => now()->subMinutes(rand(5, 60))]);
            Log::info("ConversationSeeder: Convo2 creada con ID: {$convo2->id}");

            $idsToAttachConvo2 = $usersForConvo2->pluck('id')->toArray();
            Log::info("ConversationSeeder: IDs para adjuntar a Convo2 (ID {$convo2->id}): " . print_r($idsToAttachConvo2, true));

            if (in_array(null, $idsToAttachConvo2, true)) {
                Log::error("ConversationSeeder: ¡NULO DETECTADO para Convo2 (ID {$convo2->id})! IDs: " . print_r($idsToAttachConvo2, true));
                Log::error("ConversationSeeder: Colección usersForConvo2: " . print_r($usersForConvo2->toArray(), true));
                dd("NULO DETECTADO para Convo2", $idsToAttachConvo2, $usersForConvo2); // DETENER EJECUCIÓN
            }
            $convo2->users()->attach($idsToAttachConvo2);
            $this->command->info("Conversación 2 creada (ID {$convo2->id}) entre: {$usersForConvo2[0]->name} y {$usersForConvo2[1]->name}.");
        } else {
            $this->command->warn('No se encontraron suficientes usuarios distintos para crear la Conversación 2.');
            Log::warning("ConversationSeeder: No suficientes usuarios para Convo2 (encontrados: {$usersForConvo2->count()}). Intentando fallback si es posible.");

            if (empty($idsToExcludeForConvo2) && User::count() >= 2) {
                $anyTwoUsers = User::inRandomOrder()->take(2)->get();
                Log::info("ConversationSeeder: Usuarios para fallback Convo (count {$anyTwoUsers->count()}): " . print_r($anyTwoUsers->pluck('id')->toArray(), true));

                if ($anyTwoUsers->count() == 2) {
                    $convoFallback = Conversation::create(['last_message_at' => now()->subMinutes(rand(5, 60))]);
                    Log::info("ConversationSeeder: Convo Fallback creada con ID: {$convoFallback->id}");

                    $idsToAttachFallback = $anyTwoUsers->pluck('id')->toArray();
                    Log::info("ConversationSeeder: IDs para adjuntar a Convo Fallback (ID {$convoFallback->id}): " . print_r($idsToAttachFallback, true));

                    if (in_array(null, $idsToAttachFallback, true)) {
                        Log::error("ConversationSeeder: ¡NULO DETECTADO para Convo Fallback (ID {$convoFallback->id})! IDs: " . print_r($idsToAttachFallback, true));
                        Log::error("ConversationSeeder: Colección anyTwoUsers: " . print_r($anyTwoUsers->toArray(), true));
                        dd("NULO DETECTADO para Convo Fallback", $idsToAttachFallback, $anyTwoUsers); // DETENER EJECUCIÓN
                    }
                    $convoFallback->users()->attach($idsToAttachFallback);
                    $this->command->info("Conversación 2 (fallback) creada (ID {$convoFallback->id}) entre: {$anyTwoUsers[0]->name} y {$anyTwoUsers[1]->name}.");
                } else {
                    $this->command->warn('No se encontraron suficientes usuarios distintos para crear la Conversación 2 (fallback).');
                    Log::warning("ConversationSeeder: No suficientes usuarios para Convo Fallback (encontrados: {$anyTwoUsers->count()}).");
                }
            }
        }
        $this->command->info('ConversationSeeder completado.');
        Log::info('ConversationSeeder: Completado exitosamente.');
    }
}
