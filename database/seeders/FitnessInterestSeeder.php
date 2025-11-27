<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FitnessInterest;

class FitnessInterestSeeder extends Seeder
{
    public function run(): void
    {
        $interests = [
            ['name' => 'Musculación'],
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

        foreach ($interests as $interest) {
            FitnessInterest::firstOrCreate($interest);
        }

        $this->command->info('✅ Fitness interests created successfully!');
    }
}