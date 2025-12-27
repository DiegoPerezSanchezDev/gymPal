<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Gimnasio',
                'icon' => '🏋️',
                'color' => '#6366F1', // Indigo
            ],
            [
                'name' => 'Crossfit',
                'icon' => '🔥',
                'color' => '#BE123C', // Crimson/Bordó
            ],
            [
                'name' => 'Calistenia',
                'icon' => '🤸',
                'color' => '#10B981', // Emerald
            ],
            [
                'name' => 'Yoga',
                'icon' => '🧘',
                'color' => '#A855F7', // Purple/Lavender
            ],
            [
                'name' => 'Cardio',
                'icon' => '🏃',
                'color' => '#0EA5E9', // Sky Blue
            ],
            [
                'name' => 'Funcional',
                'icon' => '⚡',
                'color' => '#EAB308', // Yellow/Gold
            ],
            [
                'name' => 'Deportes',
                'icon' => '⚽',
                'color' => '#EC4899', // Pink
            ],
            [
                'name' => 'Fuerza',
                'icon' => '💪',
                'color' => '#F97316', // Bright Orange
            ],
            [
                'name' => 'Otros',
                'icon' => '✨',
                'color' => '#475569', // Gris azulado
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'icon' => $category['icon'],
                    'color' => $category['color'],
                ]
            );
        }
    }
}
