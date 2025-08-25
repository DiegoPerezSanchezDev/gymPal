<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FitnessInterest;
use Illuminate\Support\Str;

class FitnessInterestSeeder extends Seeder
{
    public function run(): void
    {
        $interests = [
            'Levantamiento de Pesas',
            'Calistenia',
            'CrossFit',
            'Yoga',
            'Pilates',
            'Running',
            'Ciclismo',
            'Natación',
            'Zumba',
            'HIIT',
            'Entrenamiento Funcional',
            'Powerlifting',
            'Culturismo',
            'Senderismo',
            'Artes Marciales',
            'Baile Fitness',
            'Spinning',
            'Escalada',
        ];

        foreach ($interests as $interestName) {
            FitnessInterest::firstOrCreate(
                ['name' => $interestName],
                ['slug' => Str::slug($interestName)]
            );
        }
    }
}