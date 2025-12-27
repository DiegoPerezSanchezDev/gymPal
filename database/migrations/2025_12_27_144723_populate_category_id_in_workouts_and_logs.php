<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Category;
use App\Models\Workout;
use App\Models\WorkoutLog;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Asegurarnos de que las categorías existen
        Artisan::call('db:seed', ['--class' => 'CategorySeeder']);

        $categories = Category::all();

        // 2. Mapear Workouts existentes
        foreach (Workout::all() as $workout) {
            if (!$workout->category_id) {
                // Intentar buscar por nombre o slug
                $cleanCat = Str::slug($workout->category);
                $category = $categories->first(function($c) use ($workout, $cleanCat) {
                    return Str::slug($c->name) === $cleanCat || $c->slug === $workout->category;
                });

                if ($category) {
                    $workout->update(['category_id' => $category->id]);
                } else {
                    // Si no existe, asignar a "Otros" (el último del seeder por defecto)
                    $otros = Category::where('slug', 'otros')->first();
                    if ($otros) {
                        $workout->update(['category_id' => $otros->id]);
                    }
                }
            }
        }

        // 3. Mapear WorkoutLogs existentes basándose en el Workout padre o en su propio campo category
        foreach (WorkoutLog::all() as $log) {
            if (!$log->category_id) {
                // Caso A: Tiene workout_id
                if ($log->workout_id) {
                    $workout = Workout::find($log->workout_id);
                    if ($workout && $workout->category_id) {
                        $log->update(['category_id' => $workout->category_id]);
                        continue;
                    }
                }

                // Caso B: No tiene workout o el workout no tiene categoría aún
                // Intentar buscar por el campo workout_name o si tuviera un campo category antiguo
                $logCat = Str::slug($log->workout_name); // Algunos logs viejos guardaban la categoría en el nombre a veces
                $category = $categories->first(function($c) use ($logCat) {
                    return Str::slug($c->name) === $logCat;
                });

                if ($category) {
                    $log->update(['category_id' => $category->id]);
                } else {
                    $otros = Category::where('slug', 'otros')->first();
                    if ($otros) {
                        $log->update(['category_id' => $otros->id]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Workout::query()->update(['category_id' => null]);
        WorkoutLog::query()->update(['category_id' => null]);
    }
};
