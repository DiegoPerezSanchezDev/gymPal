<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutExercise;

class LauraWorkoutSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar el usuario user_sol
        $user = User::where('username', 'user_sol')->first();
        
        if (!$user) {
            $this->command->warn('⚠️  Usuario @user_sol no encontrado. Ejecuta TestUsersSeeder primero.');
            return;
        }

        // Crear rutina: Push Day (Día de empuje)
        $pushDay = Workout::create([
            'user_id' => $user->id,
            'name' => 'Push Day - Pecho, Hombros y Tríceps',
            'description' => 'Rutina de empuje enfocada en pecho, hombros y tríceps. Perfecta para desarrollar la parte superior del cuerpo.',
            'difficulty' => 'Intermedio',
            'duration_minutes' => 75,
            'category' => '🏋️ Gym',
            'is_public' => true,
        ]);

        // Ejercicio 1: Press Banca
        WorkoutExercise::create([
            'workout_id' => $pushDay->id,
            'exercise_name' => 'Press de Banca con Barra',
            'sets_data' => [
                ['reps' => 12, 'weight' => 40, 'type' => 'warmup'],
                ['reps' => 10, 'weight' => 50, 'type' => 'normal'],
                ['reps' => 8, 'weight' => 55, 'type' => 'normal'],
                ['reps' => 6, 'weight' => 60, 'type' => 'normal'],
            ],
            'rest_seconds' => 90,
            'notes' => 'Mantener la espalda pegada al banco y controlar la bajada',
            'order' => 0,
        ]);

        // Ejercicio 2: Press Inclinado con Mancuernas
        WorkoutExercise::create([
            'workout_id' => $pushDay->id,
            'exercise_name' => 'Press Inclinado con Mancuernas',
            'sets_data' => [
                ['reps' => 12, 'weight' => 16, 'type' => 'normal'],
                ['reps' => 10, 'weight' => 18, 'type' => 'normal'],
                ['reps' => 10, 'weight' => 18, 'type' => 'normal'],
                ['reps' => 8, 'weight' => 20, 'type' => 'normal'],
            ],
            'rest_seconds' => 75,
            'notes' => 'Banco a 30-45 grados, enfoque en pecho superior',
            'order' => 1,
        ]);

        // Ejercicio 3: Aperturas con Mancuernas
        WorkoutExercise::create([
            'workout_id' => $pushDay->id,
            'exercise_name' => 'Aperturas con Mancuernas',
            'sets_data' => [
                ['reps' => 15, 'weight' => 10, 'type' => 'normal'],
                ['reps' => 12, 'weight' => 12, 'type' => 'normal'],
                ['reps' => 12, 'weight' => 12, 'type' => 'normal'],
                ['reps' => 10, 'weight' => 14, 'type' => 'normal'],
            ],
            'rest_seconds' => 60,
            'notes' => 'Movimiento controlado, sentir el estiramiento en el pecho',
            'order' => 2,
        ]);

        // Ejercicio 4: Press Militar con Barra
        WorkoutExercise::create([
            'workout_id' => $pushDay->id,
            'exercise_name' => 'Press Militar con Barra',
            'sets_data' => [
                ['reps' => 12, 'weight' => 25, 'type' => 'warmup'],
                ['reps' => 10, 'weight' => 30, 'type' => 'normal'],
                ['reps' => 8, 'weight' => 35, 'type' => 'normal'],
                ['reps' => 8, 'weight' => 35, 'type' => 'normal'],
            ],
            'rest_seconds' => 90,
            'notes' => 'Core activado, no arquear la espalda',
            'order' => 3,
        ]);

        // Ejercicio 5: Elevaciones Laterales
        WorkoutExercise::create([
            'workout_id' => $pushDay->id,
            'exercise_name' => 'Elevaciones Laterales con Mancuernas',
            'sets_data' => [
                ['reps' => 15, 'weight' => 6, 'type' => 'normal'],
                ['reps' => 12, 'weight' => 8, 'type' => 'normal'],
                ['reps' => 12, 'weight' => 8, 'type' => 'normal'],
                ['reps' => 10, 'weight' => 10, 'type' => 'normal'],
            ],
            'rest_seconds' => 60,
            'notes' => 'Codos ligeramente flexionados, subir hasta altura de hombros',
            'order' => 4,
        ]);

        // Ejercicio 6: Fondos en Paralelas
        WorkoutExercise::create([
            'workout_id' => $pushDay->id,
            'exercise_name' => 'Fondos en Paralelas (Tríceps)',
            'sets_data' => [
                ['reps' => 12, 'weight' => 0, 'type' => 'normal'],
                ['reps' => 10, 'weight' => 0, 'type' => 'normal'],
                ['reps' => 8, 'weight' => 0, 'type' => 'normal'],
                ['reps' => 15, 'weight' => 0, 'type' => 'failure'],
            ],
            'rest_seconds' => 75,
            'notes' => 'Inclinarse hacia adelante para enfatizar tríceps',
            'order' => 5,
        ]);

        // Ejercicio 7: Extensiones de Tríceps
        WorkoutExercise::create([
            'workout_id' => $pushDay->id,
            'exercise_name' => 'Extensiones de Tríceps en Polea',
            'sets_data' => [
                ['reps' => 15, 'weight' => 20, 'type' => 'normal'],
                ['reps' => 12, 'weight' => 25, 'type' => 'normal'],
                ['reps' => 12, 'weight' => 25, 'type' => 'normal'],
                ['reps' => 20, 'weight' => 15, 'type' => 'drop'],
            ],
            'rest_seconds' => 45,
            'notes' => 'Codos pegados al cuerpo, última serie drop set',
            'order' => 6,
        ]);

        $this->command->info('✅ Rutina "Push Day" creada para @laurag con 7 ejercicios');

        // Crear rutina 2: Pull Day (Día de tirón)
        $pullDay = Workout::create([
            'user_id' => $user->id,
            'name' => 'Pull Day - Espalda y Bíceps',
            'description' => 'Rutina de tirón para desarrollar una espalda fuerte y bíceps definidos.',
            'difficulty' => 'Intermedio',
            'duration_minutes' => 70,
            'category' => '🏋️ Gym',
            'is_public' => true,
        ]);

        // Pull Day - Ejercicio 1: Dominadas
        WorkoutExercise::create([
            'workout_id' => $pullDay->id,
            'exercise_name' => 'Dominadas Agarre Pronado',
            'sets_data' => [
                ['reps' => 8, 'weight' => 0, 'type' => 'normal'],
                ['reps' => 6, 'weight' => 0, 'type' => 'normal'],
                ['reps' => 6, 'weight' => 0, 'type' => 'normal'],
                ['reps' => 10, 'weight' => 0, 'type' => 'failure'],
            ],
            'rest_seconds' => 120,
            'notes' => 'Si es muy difícil, usar banda elástica de asistencia',
            'order' => 0,
        ]);

        // Pull Day - Ejercicio 2: Remo con Barra
        WorkoutExercise::create([
            'workout_id' => $pullDay->id,
            'exercise_name' => 'Remo con Barra',
            'sets_data' => [
                ['reps' => 12, 'weight' => 35, 'type' => 'warmup'],
                ['reps' => 10, 'weight' => 45, 'type' => 'normal'],
                ['reps' => 8, 'weight' => 50, 'type' => 'normal'],
                ['reps' => 8, 'weight' => 50, 'type' => 'normal'],
            ],
            'rest_seconds' => 90,
            'notes' => 'Espalda recta, llevar barra al abdomen',
            'order' => 1,
        ]);

        // Pull Day - Ejercicio 3: Jalón al Pecho
        WorkoutExercise::create([
            'workout_id' => $pullDay->id,
            'exercise_name' => 'Jalón al Pecho en Polea',
            'sets_data' => [
                ['reps' => 12, 'weight' => 40, 'type' => 'normal'],
                ['reps' => 10, 'weight' => 45, 'type' => 'normal'],
                ['reps' => 10, 'weight' => 45, 'type' => 'normal'],
                ['reps' => 8, 'weight' => 50, 'type' => 'normal'],
            ],
            'rest_seconds' => 75,
            'notes' => 'Agarre amplio, llevar barra al pecho superior',
            'order' => 2,
        ]);

        // Pull Day - Ejercicio 4: Curl con Barra Z
        WorkoutExercise::create([
            'workout_id' => $pullDay->id,
            'exercise_name' => 'Curl con Barra Z',
            'sets_data' => [
                ['reps' => 12, 'weight' => 20, 'type' => 'normal'],
                ['reps' => 10, 'weight' => 25, 'type' => 'normal'],
                ['reps' => 8, 'weight' => 27.5, 'type' => 'normal'],
                ['reps' => 15, 'weight' => 15, 'type' => 'drop'],
            ],
            'rest_seconds' => 60,
            'notes' => 'Codos fijos, movimiento controlado',
            'order' => 3,
        ]);

        // Pull Day - Ejercicio 5: Curl Martillo
        WorkoutExercise::create([
            'workout_id' => $pullDay->id,
            'exercise_name' => 'Curl Martillo con Mancuernas',
            'sets_data' => [
                ['reps' => 12, 'weight' => 12, 'type' => 'normal'],
                ['reps' => 10, 'weight' => 14, 'type' => 'normal'],
                ['reps' => 10, 'weight' => 14, 'type' => 'normal'],
                ['reps' => 8, 'weight' => 16, 'type' => 'normal'],
            ],
            'rest_seconds' => 60,
            'notes' => 'Agarre neutro, trabajar braquial',
            'order' => 4,
        ]);

        $this->command->info('✅ Rutina "Pull Day" creada para @laurag con 5 ejercicios');

        // Incrementar contador de rutinas guardadas
        $pushDay->increment('times_saved', 5);
        $pullDay->increment('times_saved', 3);

        $this->command->info('🎉 Seeders completados exitosamente para @laurag');
    }
}
