<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
    public function definition(): array
{
    return [
        'name' => fake()->name(),
        'username' => fake()->unique()->userName(),
        'display_name' => fake()->firstName() . ' ' . fake()->lastName(),
        'email' => fake()->unique()->safeEmail(),
        'email_verified_at' => now(),
        'password' => static::$password ??= Hash::make('password'), // o solo Hash::make('password')
        'remember_token' => Str::random(10),
        'location_city' => fake()->city(),
        'bio' => fake()->sentence(10),
        'experience_level' => fake()->randomElement(['Principiante', 'Intermedio', 'Avanzado']),
        'availability_general' => fake()->randomElement(['Mañanas', 'Tardes', 'Noches', 'Fines de semana']),
    ];
}
}
