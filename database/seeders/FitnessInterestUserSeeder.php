<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\FitnessInterest;

class FitnessInterestUserSeeder extends Seeder
{
    public function run(): void
    {
        $allInterestIds = FitnessInterest::pluck('id')->toArray();

        foreach (User::all() as $user) {
            // Asigna entre 2 y 5 intereses aleatorios a cada usuario
            $randomInterests = collect($allInterestIds)->random(rand(2, min(5, count($allInterestIds))))->toArray();
            $user->fitnessInterests()->sync($randomInterests);
        }
    }
}