<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutExercise;

class WorkoutSeeder extends Seeder
{
    public function run(): void
    {
        $diego = User::where('username', 'user_plazamayor')->first();
        $laura = User::where('username', 'user_sol')->first();
        $elena = User::where('username', 'user_bernabeu')->first();

        if (!$diego || !$laura || !$elena) {
            $this->command->warn('⚠️  Usuarios no encontrados. Asegúrate de ejecutar TestUsersSeeder primero.');
            return;
        }

        // Diego's Workouts
        $pushDay = Workout::create([
            'user_id' => $diego->id,
            'name' => 'Push Day - Torso Empuje',
            'description' => 'Rutina de empuje enfocada en pecho, hombros y tríceps. Ideal para ganar fuerza y masa muscular.',
            'category' => 'Fuerza',
            'difficulty' => 'Intermedio',
            'duration_minutes' => 75,
            'is_public' => true,
        ]);

        $exercises = [
            [
                'exercise_name' => 'Press Banca Plano',
                'sets_data' => [
                    ['reps' => '6-8', 'weight' => 80, 'type' => 'normal'],
                    ['reps' => '6-8', 'weight' => 80, 'type' => 'normal'],
                    ['reps' => '6-8', 'weight' => 80, 'type' => 'normal'],
                    ['reps' => '6-8', 'weight' => 80, 'type' => 'normal']
                ],
                'rest_seconds' => 180,
                'notes' => 'Calentar bien antes. Controlar la bajada.',
                'order' => 1
            ],
            [
                'exercise_name' => 'Press Inclinado con Mancuernas',
                'sets_data' => [
                    ['reps' => '8-10', 'weight' => 32, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => 32, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => 32, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => 32, 'type' => 'normal']
                ],
                'rest_seconds' => 120,
                'notes' => 'Enfoque en pecho superior',
                'order' => 2
            ],
            [
                'exercise_name' => 'Aperturas con Mancuernas',
                'sets_data' => [
                    ['reps' => '12-15', 'weight' => 16, 'type' => 'normal'],
                    ['reps' => '12-15', 'weight' => 16, 'type' => 'normal'],
                    ['reps' => '12-15', 'weight' => 16, 'type' => 'normal']
                ],
                'rest_seconds' => 90,
                'notes' => 'Estirar bien el pectoral',
                'order' => 3
            ],
            [
                'exercise_name' => 'Press Militar con Barra',
                'sets_data' => [
                    ['reps' => '8-10', 'weight' => 50, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => 50, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => 50, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => 50, 'type' => 'normal']
                ],
                'rest_seconds' => 120,
                'notes' => 'Core apretado',
                'order' => 4
            ],
            [
                'exercise_name' => 'Elevaciones Laterales',
                'sets_data' => [
                    ['reps' => '12-15', 'weight' => 12, 'type' => 'normal'],
                    ['reps' => '12-15', 'weight' => 12, 'type' => 'normal'],
                    ['reps' => '12-15', 'weight' => 12, 'type' => 'normal']
                ],
                'rest_seconds' => 60,
                'notes' => 'Sin balanceo',
                'order' => 5
            ],
            [
                'exercise_name' => 'Fondos en Paralelas',
                'sets_data' => [
                    ['reps' => 'Al fallo', 'weight' => 0, 'type' => 'normal'],
                    ['reps' => 'Al fallo', 'weight' => 0, 'type' => 'normal'],
                    ['reps' => 'Al fallo', 'weight' => 0, 'type' => 'normal']
                ],
                'rest_seconds' => 90,
                'notes' => 'Inclinarse hacia adelante para pecho',
                'order' => 6
            ],
            [
                'exercise_name' => 'Extensiones de Tríceps en Polea',
                'sets_data' => [
                    ['reps' => '12-15', 'weight' => 30, 'type' => 'normal'],
                    ['reps' => '12-15', 'weight' => 30, 'type' => 'normal'],
                    ['reps' => '12-15', 'weight' => 30, 'type' => 'normal']
                ],
                'rest_seconds' => 60,
                'notes' => 'Codos fijos',
                'order' => 7
            ],
        ];

        foreach ($exercises as $exercise) {
            WorkoutExercise::create(array_merge(['workout_id' => $pushDay->id], $exercise));
        }

        $pullDay = Workout::create([
            'user_id' => $diego->id,
            'name' => 'Pull Day - Espalda y Bíceps',
            'description' => 'Día de tracción completo. Espalda gruesa y ancha + trabajo de bíceps.',
            'difficulty' => 'Intermedio',
            'category' => 'Hipertrofia',
            'duration_minutes' => 70,
            'is_public' => true,
        ]);

        $pullExercises = [
            [
                'exercise_name' => 'Dominadas Pronas',
                'sets_data' => [
                    ['reps' => '8-10', 'weight' => 0, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => 0, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => 0, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => 0, 'type' => 'normal']
                ],
                'rest_seconds' => 120,
                'notes' => 'Si es necesario usar banda elástica',
                'order' => 1
            ],
            [
                'exercise_name' => 'Remo con Barra',
                'sets_data' => [
                    ['reps' => '8-10', 'weight' => 70, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => 70, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => 70, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => 70, 'type' => 'normal']
                ],
                'rest_seconds' => 120,
                'notes' => 'Llevar a zona baja del abdomen',
                'order' => 2
            ],
            [
                'exercise_name' => 'Jalón al Pecho',
                'sets_data' => [
                    ['reps' => '10-12', 'weight' => 60, 'type' => 'normal'],
                    ['reps' => '10-12', 'weight' => 60, 'type' => 'normal'],
                    ['reps' => '10-12', 'weight' => 60, 'type' => 'normal']
                ],
                'rest_seconds' => 90,
                'notes' => 'Agarre amplio',
                'order' => 3
            ],
            [
                'exercise_name' => 'Remo con Mancuerna',
                'sets_data' => [
                    ['reps' => '10-12', 'weight' => 36, 'type' => 'normal'],
                    ['reps' => '10-12', 'weight' => 36, 'type' => 'normal'],
                    ['reps' => '10-12', 'weight' => 36, 'type' => 'normal']
                ],
                'rest_seconds' => 90,
                'notes' => 'Cada brazo',
                'order' => 4
            ],
            [
                'exercise_name' => 'Curl con Barra Z',
                'sets_data' => [
                    ['reps' => '10-12', 'weight' => 30, 'type' => 'normal'],
                    ['reps' => '10-12', 'weight' => 30, 'type' => 'normal'],
                    ['reps' => '10-12', 'weight' => 30, 'type' => 'normal']
                ],
                'rest_seconds' => 60,
                'notes' => 'Sin balanceo',
                'order' => 5
            ],
            [
                'exercise_name' => 'Curl Martillo',
                'sets_data' => [
                    ['reps' => '12-15', 'weight' => 16, 'type' => 'normal'],
                    ['reps' => '12-15', 'weight' => 16, 'type' => 'normal'],
                    ['reps' => '12-15', 'weight' => 16, 'type' => 'normal']
                ],
                'rest_seconds' => 60,
                'notes' => 'Trabajo de braquial',
                'order' => 6
            ],
        ];

        foreach ($pullExercises as $exercise) {
            WorkoutExercise::create(array_merge(['workout_id' => $pullDay->id], $exercise));
        }

        $legDay = Workout::create([
            'user_id' => $diego->id,
            'name' => 'Leg Day - Pierna Completa',
            'description' => 'Día de pierna intenso. Cuádriceps, femoral, glúteo y gemelos.',
            'difficulty' => 'Avanzado',
            'category' => 'Hipertrofia',
            'duration_minutes' => 90,
            'is_public' => true,
        ]);

        $legExercises = [
            [
                'exercise_name' => 'Sentadilla con Barra',
                'sets_data' => [
                    ['reps' => '6-8', 'weight' => 100, 'type' => 'normal'],
                    ['reps' => '6-8', 'weight' => 100, 'type' => 'normal'],
                    ['reps' => '6-8', 'weight' => 100, 'type' => 'normal'],
                    ['reps' => '6-8', 'weight' => 100, 'type' => 'normal'],
                    ['reps' => '6-8', 'weight' => 100, 'type' => 'normal']
                ],
                'rest_seconds' => 180,
                'notes' => 'Profundidad completa',
                'order' => 1
            ],
            [
                'exercise_name' => 'Prensa de Pierna',
                'sets_data' => [
                    ['reps' => '10-12', 'weight' => 180, 'type' => 'normal'],
                    ['reps' => '10-12', 'weight' => 180, 'type' => 'normal'],
                    ['reps' => '10-12', 'weight' => 180, 'type' => 'normal'],
                    ['reps' => '10-12', 'weight' => 180, 'type' => 'normal']
                ],
                'rest_seconds' => 120,
                'notes' => 'Pies ancho de hombros',
                'order' => 2
            ],
            [
                'exercise_name' => 'Peso Muerto Rumano',
                'sets_data' => [
                    ['reps' => '8-10', 'weight' => 80, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => 80, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => 80, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => 80, 'type' => 'normal']
                ],
                'rest_seconds' => 120,
                'notes' => 'Enfoque en femoral',
                'order' => 3
            ],
            [
                'exercise_name' => 'Zancadas con Mancuernas',
                'sets_data' => [
                    ['reps' => '12', 'weight' => 20, 'type' => 'normal'],
                    ['reps' => '12', 'weight' => 20, 'type' => 'normal'],
                    ['reps' => '12', 'weight' => 20, 'type' => 'normal']
                ],
                'rest_seconds' => 90,
                'notes' => 'Rodilla no pasa la punta del pie',
                'order' => 4
            ],
            [
                'exercise_name' => 'Curl Femoral Tumbado',
                'sets_data' => [
                    ['reps' => '12-15', 'weight' => 40, 'type' => 'normal'],
                    ['reps' => '12-15', 'weight' => 40, 'type' => 'normal'],
                    ['reps' => '12-15', 'weight' => 40, 'type' => 'normal']
                ],
                'rest_seconds' => 60,
                'notes' => 'Contracción máxima arriba',
                'order' => 5
            ],
            [
                'exercise_name' => 'Extensión de Cuádriceps',
                'sets_data' => [
                    ['reps' => '12-15', 'weight' => 50, 'type' => 'normal'],
                    ['reps' => '12-15', 'weight' => 50, 'type' => 'normal'],
                    ['reps' => '12-15', 'weight' => 50, 'type' => 'normal']
                ],
                'rest_seconds' => 60,
                'notes' => 'Última serie al fallo',
                'order' => 6
            ],
            [
                'exercise_name' => 'Elevaciones de Gemelo de Pie',
                'sets_data' => [
                    ['reps' => '15-20', 'weight' => 60, 'type' => 'normal'],
                    ['reps' => '15-20', 'weight' => 60, 'type' => 'normal'],
                    ['reps' => '15-20', 'weight' => 60, 'type' => 'normal'],
                    ['reps' => '15-20', 'weight' => 60, 'type' => 'normal']
                ],
                'rest_seconds' => 45,
                'notes' => 'Rango completo',
                'order' => 7
            ],
        ];

        foreach ($legExercises as $exercise) {
            WorkoutExercise::create(array_merge(['workout_id' => $legDay->id], $exercise));
        }

        // Laura's Workout
        $upperBody = Workout::create([
            'user_id' => $laura->id,
            'name' => 'Upper Body Strength',
            'description' => 'Rutina de tren superior para mujeres. Enfoque en fuerza funcional.',
            'difficulty' => 'Intermedio',
            'category' => 'Fuerza',
            'duration_minutes' => 60,
            'is_public' => true,
        ]);

        $upperExercises = [
            [
                'exercise_name' => 'Press Banca con Mancuernas',
                'sets_data' => [
                    ['reps' => '8-10', 'weight' => 16, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => 16, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => 16, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => 16, 'type' => 'normal']
                ],
                'rest_seconds' => 90,
                'notes' => 'Mayor rango de movimiento',
                'order' => 1
            ],
            [
                'exercise_name' => 'Remo en Polea Baja',
                'sets_data' => [
                    ['reps' => '10-12', 'weight' => 40, 'type' => 'normal'],
                    ['reps' => '10-12', 'weight' => 40, 'type' => 'normal'],
                    ['reps' => '10-12', 'weight' => 40, 'type' => 'normal'],
                    ['reps' => '10-12', 'weight' => 40, 'type' => 'normal']
                ],
                'rest_seconds' => 90,
                'notes' => 'Espalda recta',
                'order' => 2
            ],
            [
                'exercise_name' => 'Press Arnold',
                'sets_data' => [
                    ['reps' => '10-12', 'weight' => 10, 'type' => 'normal'],
                    ['reps' => '10-12', 'weight' => 10, 'type' => 'normal'],
                    ['reps' => '10-12', 'weight' => 10, 'type' => 'normal']
                ],
                'rest_seconds' => 60,
                'notes' => 'Rotación completa',
                'order' => 3
            ],
            [
                'exercise_name' => 'Dominadas Asistidas',
                'sets_data' => [
                    ['reps' => '8-10', 'weight' => -20, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => -20, 'type' => 'normal'],
                    ['reps' => '8-10', 'weight' => -20, 'type' => 'normal']
                ],
                'rest_seconds' => 90,
                'notes' => 'Progresión hacia dominadas sin asistencia',
                'order' => 4
            ],
            [
                'exercise_name' => 'Fondos en Banco',
                'sets_data' => [
                    ['reps' => '12-15', 'weight' => 0, 'type' => 'normal'],
                    ['reps' => '12-15', 'weight' => 0, 'type' => 'normal'],
                    ['reps' => '12-15', 'weight' => 0, 'type' => 'normal']
                ],
                'rest_seconds' => 60,
                'notes' => 'Enfoque en tríceps',
                'order' => 5
            ],
        ];

        foreach ($upperExercises as $exercise) {
            WorkoutExercise::create(array_merge(['workout_id' => $upperBody->id], $exercise));
        }

        // Elena's Crossfit WOD
        $wod = Workout::create([
            'user_id' => $elena->id,
            'name' => 'Cindy WOD',
            'description' => 'Clásico WOD de Crossfit. 20 minutos AMRAP (As Many Rounds As Possible)',
            'difficulty' => 'Intermedio',
            'category' => 'Crossfit',
            'duration_minutes' => 20,
            'is_public' => true,
        ]);

        $wodExercises = [
            [
                'exercise_name' => 'Pull-ups',
                'sets_data' => [['reps' => '5', 'weight' => 0, 'type' => 'normal']],
                'rest_seconds' => 0,
                'notes' => 'Repetir el circuito tantas veces como sea posible en 20 min',
                'order' => 1
            ],
            [
                'exercise_name' => 'Push-ups',
                'sets_data' => [['reps' => '10', 'weight' => 0, 'type' => 'normal']],
                'rest_seconds' => 0,
                'notes' => 'Pecho al suelo',
                'order' => 2
            ],
            [
                'exercise_name' => 'Air Squats',
                'sets_data' => [['reps' => '15', 'weight' => 0, 'type' => 'normal']],
                'rest_seconds' => 0,
                'notes' => 'Profundidad completa',
                'order' => 3
            ],
        ];

        foreach ($wodExercises as $exercise) {
            WorkoutExercise::create(array_merge(['workout_id' => $wod->id], $exercise));
        }

        $this->command->info('✅ Workouts created successfully!');
    }
}
