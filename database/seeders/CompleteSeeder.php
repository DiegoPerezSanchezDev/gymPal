<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Models\FitnessInterest;
use App\Models\Connection;
use App\Models\Comment;
use Illuminate\Support\Facades\Hash;

class CompleteSeeder extends Seeder
{
    public function run()
    {
        // 1. Crear Intereses de Fitness
        $interests = [
            ['name' => 'Gym'],
            ['name' => 'Crossfit'],
            ['name' => 'Running'],
            ['name' => 'Yoga'],
            ['name' => 'Calistenia'],
            ['name' => 'Powerlifting'],
            ['name' => 'Ciclismo'],
            ['name' => 'Natación'],
            ['name' => 'Boxeo'],
            ['name' => 'Pilates'],
        ];

        $interestModels = [];
        foreach ($interests as $interest) {
            $interestModels[$interest['name']] = FitnessInterest::firstOrCreate($interest);
        }

        // 2. Crear TU Usuario (Perfil Principal)
        $me = User::updateOrCreate(
            ['email' => 'diego@example.com'],
            [
                'name' => 'Diego Pérez',
                'username' => 'diegoperez',
                'password' => Hash::make('password'),
                'bio' => 'Apasionado del fitness y la tecnología. Buscando superar mis límites cada día. 🚀',
                'location_city' => 'Madrid, España',
                'experience_level' => 'Avanzado',
                'availability_general' => json_encode(['Tarde', 'Finde semana']),
                'looking_for_interest_id' => $interestModels['Gym']->id,
                'banner_color' => '#4F46E5', // Indigo
                'latitude' => 40.4168,
                'longitude' => -3.7038,
            ]
        );

        $me->fitnessInterests()->sync([
            $interestModels['Gym']->id,
            $interestModels['Crossfit']->id,
            $interestModels['Running']->id
        ]);

        // 3. Crear Otros Usuarios
        $usersData = [
            [
                'name' => 'Laura García',
                'username' => 'laurafit',
                'email' => 'laura@example.com',
                'bio' => 'Entrenadora personal. Ayudo a mujeres a ganar fuerza. 💪',
                'location_city' => 'Barcelona, España',
                'experience_level' => 'Experto',
                'interest' => 'Gym',
                'looking_for' => 'Yoga',
                'color' => '#EC4899',
                'availability' => ['Mañana', 'Tarde'],
                'lat' => 41.3851,
                'lng' => 2.1734,
            ],
            [
                'name' => 'Carlos Rodríguez',
                'username' => 'carlosrun',
                'email' => 'carlos@example.com',
                'bio' => 'Maratoniano en proceso. 42km is the goal. 🏃‍♂️',
                'location_city' => 'Valencia, España',
                'experience_level' => 'Intermedio',
                'interest' => 'Running',
                'looking_for' => 'Running',
                'color' => '#10B981',
                'availability' => ['Finde semana', 'Noche'],
                'lat' => 39.4699,
                'lng' => -0.3763,
            ],
            [
                'name' => 'Ana Martínez',
                'username' => 'anamyoga',
                'email' => 'ana@example.com',
                'bio' => 'Namasté. Profesora de Vinyasa Yoga. 🧘‍♀️',
                'location_city' => 'Madrid, España',
                'experience_level' => 'Experto',
                'interest' => 'Yoga',
                'looking_for' => null,
                'color' => '#8B5CF6',
                'availability' => ['Mañana'],
                'lat' => 40.4168,
                'lng' => -3.7038,
            ],
            [
                'name' => 'David López',
                'username' => 'davidcalis',
                'email' => 'david@example.com',
                'bio' => 'Dominando mi propio peso corporal. Street Workout. 🤸',
                'location_city' => 'Sevilla, España',
                'experience_level' => 'Avanzado',
                'interest' => 'Calistenia',
                'looking_for' => 'Calistenia',
                'color' => '#F59E0B',
                'availability' => ['Tarde', 'Noche'],
                'lat' => 37.3886,
                'lng' => -5.9823,
            ],
            [
                'name' => 'Elena Torres',
                'username' => 'elenacross',
                'email' => 'elena@example.com',
                'bio' => 'WOD tras WOD. Crossfit lover. ❤️‍🔥',
                'location_city' => 'Bilbao, España',
                'experience_level' => 'Intermedio',
                'interest' => 'Crossfit',
                'looking_for' => 'Powerlifting',
                'color' => '#EF4444',
                'availability' => ['Mañana', 'Finde semana'],
                'lat' => 43.2630,
                'lng' => -2.9350,
            ],
        ];

        $otherUsers = [];
        foreach ($usersData as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => Hash::make('password'),
                'bio' => $userData['bio'],
                'location_city' => $userData['location_city'],
                'experience_level' => $userData['experience_level'],
                'looking_for_interest_id' => $userData['looking_for'] ? $interestModels[$userData['looking_for']]->id : null,
                'banner_color' => $userData['color'],
                'availability_general' => json_encode($userData['availability']),
                'latitude' => $userData['lat'],
                'longitude' => $userData['lng'],
            ]);

            $user->fitnessInterests()->sync([
                $interestModels[$userData['interest']]->id
            ]);

            $otherUsers[] = $user;
        }

        // 4. Crear Conexiones (GymPals)
        Connection::create(['sender_id' => $me->id, 'receiver_id' => $otherUsers[0]->id, 'status' => 'accepted']);
        Connection::create(['sender_id' => $otherUsers[0]->id, 'receiver_id' => $me->id, 'status' => 'accepted']);

        Connection::create(['sender_id' => $me->id, 'receiver_id' => $otherUsers[1]->id, 'status' => 'accepted']);
        Connection::create(['sender_id' => $otherUsers[1]->id, 'receiver_id' => $me->id, 'status' => 'accepted']);

        // 5. Crear Rutinas para Diego
        $workout1 = Workout::create([
            'user_id' => $me->id,
            'name' => 'Torso Fuerza',
            'description' => 'Rutina enfocada en básicos de empuje y tracción.',
            'category' => 'Fuerza',
            'difficulty' => 'Intermedio',
            'duration_minutes' => 75,
            'is_public' => true,
        ]);

        // Ejercicios con sets_data en formato JSON
        WorkoutExercise::create([
            'workout_id' => $workout1->id,
            'exercise_name' => 'Press Banca',
            'sets_data' => json_encode([
                ['reps' => 8, 'weight' => 80],
                ['reps' => 8, 'weight' => 80],
                ['reps' => 6, 'weight' => 85],
                ['reps' => 6, 'weight' => 85],
            ]),
            'rest_seconds' => 120,
            'order' => 1
        ]);

        WorkoutExercise::create([
            'workout_id' => $workout1->id,
            'exercise_name' => 'Remo con Barra',
            'sets_data' => json_encode([
                ['reps' => 10, 'weight' => 60],
                ['reps' => 10, 'weight' => 60],
                ['reps' => 8, 'weight' => 65],
                ['reps' => 8, 'weight' => 65],
            ]),
            'rest_seconds' => 90,
            'order' => 2
        ]);

        WorkoutExercise::create([
            'workout_id' => $workout1->id,
            'exercise_name' => 'Press Militar',
            'sets_data' => json_encode([
                ['reps' => 10, 'weight' => 40],
                ['reps' => 10, 'weight' => 40],
                ['reps' => 8, 'weight' => 42.5],
            ]),
            'rest_seconds' => 90,
            'order' => 3
        ]);

        WorkoutExercise::create([
            'workout_id' => $workout1->id,
            'exercise_name' => 'Dominadas',
            'sets_data' => json_encode([
                ['reps' => 12, 'weight' => 0],
                ['reps' => 10, 'weight' => 0],
                ['reps' => 8, 'weight' => 0],
            ]),
            'rest_seconds' => 120,
            'order' => 4
        ]);

        $workout2 = Workout::create([
            'user_id' => $me->id,
            'name' => 'Pierna Hipertrofia',
            'description' => 'Día de pierna intenso. Enfocado en cuádriceps.',
            'category' => 'Hipertrofia',
            'difficulty' => 'Avanzado',
            'duration_minutes' => 90,
            'is_public' => true,
        ]);

        WorkoutExercise::create([
            'workout_id' => $workout2->id,
            'exercise_name' => 'Sentadilla',
            'sets_data' => json_encode([
                ['reps' => 10, 'weight' => 100],
                ['reps' => 10, 'weight' => 100],
                ['reps' => 8, 'weight' => 110],
                ['reps' => 8, 'weight' => 110],
            ]),
            'rest_seconds' => 180,
            'order' => 1
        ]);

        // 6. Crear Posts
        $postDiegoTexto = Post::create([
            'user_id' => $me->id,
            'content' => "Hoy ha sido uno de esos días donde no apetece entrenar, pero se cumple igual. La disciplina es hacer lo que tienes que hacer, cuando no quieres hacerlo. 👊 #NoExcuses #GymPal",
        ]);

        $postDiegoRutina = Post::create([
            'user_id' => $me->id,
            'content' => "Os comparto mi rutina de Torso de hoy. He subido marcas en banca! 🚀",
            'workout_id' => $workout1->id,
        ]);

        $postLaura = Post::create([
            'user_id' => $otherUsers[0]->id,
            'content' => "Chicas, recordad que el entrenamiento de fuerza NO os hará parecer hombres. Os hará fuertes, funcionales y seguras. 💖 #GirlPower #Strength",
        ]);

        Post::create([
            'user_id' => $otherUsers[1]->id,
            'content' => "Tirada larga de domingo completada. 18km a ritmo suave. Preparando motores para Valencia! 🥘🏃",
        ]);

        $postDavid = Post::create([
            'user_id' => $otherUsers[3]->id,
            'content' => "Primer Muscle Up limpio!! Llevaba meses persiguiéndolo. 🔥",
        ]);

        // 7. INTERACCIONES REALES (Likes y Comentarios)
        
        // Laura y Carlos le dan like al post de texto de Diego
        $postDiegoTexto->likers()->attach([$otherUsers[0]->id, $otherUsers[1]->id]);

        // Laura comenta
        Comment::create([
            'post_id' => $postDiegoTexto->id,
            'user_id' => $otherUsers[0]->id,
            'body' => 'Totalmente de acuerdo Diego! La constancia es la clave. 🔥'
        ]);

        // Elena, David y Ana le dan like al post de rutina de Diego
        $postDiegoRutina->likers()->attach([$otherUsers[4]->id, $otherUsers[3]->id, $otherUsers[2]->id]);

        // Carlos comenta
        Comment::create([
            'post_id' => $postDiegoRutina->id,
            'user_id' => $otherUsers[1]->id,
            'body' => 'Buena rutina! Me la guardo para probarla un día.'
        ]);

        // Diego le da like al post de Laura
        $postLaura->likers()->attach($me->id);

        // Diego comenta al post de Laura
        Comment::create([
            'post_id' => $postLaura->id,
            'user_id' => $me->id,
            'body' => 'Gran mensaje Laura! 💪'
        ]);

        // Diego le da like al post de David
        $postDavid->likers()->attach($me->id);

        $this->command->info('✅ Base de datos poblada exitosamente!');
        $this->command->info('📧 Email: diego@example.com');
        $this->command->info('🔑 Password: password');
    }
}
