<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // --- Campos de Información Básica ---
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            // 'username' no se valida aquí porque lo hemos puesto como 'readonly' en el formulario.
            // Si decidieras hacerlo editable, necesitarías añadir su regla de validación.
            'username' => [
            'required', 'string', 'lowercase', 'alpha_dash', 'max:255',
            Rule::unique(User::class)->ignore($this->user()->id),
            ],

            // --- Campos de Perfil Público ---
            'display_name' => ['nullable', 'string', 'max:255'],
            'location_city' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],

            // --- Campos de Fitness y Búsqueda (LOS NUEVOS) ---

            // Valida que el nivel de experiencia sea uno de los valores permitidos.
            'experience_level' => [
                'nullable', 
                'string', 
                Rule::in(['Principiante', 'Intermedio', 'Avanzado'])
            ],

            // Valida que la disponibilidad sea un array. Cada elemento no debe superar los 50 caracteres.
            'availability_general' => ['nullable', 'array'],

            // Valida que 'interests' sea un array.
            'interests' => ['nullable', 'array'],
            // Valida que CADA elemento dentro del array 'interests' sea un número entero
            // y que exista como 'id' en la tabla 'fitness_interests'.
            'interests.*' => ['integer', 'exists:fitness_interests,id'],

            // Valida que el interés principal para buscar compañero sea un ID válido.
            'looking_for_interest_id' => [
                'nullable', 
                'integer', 
                'exists:fitness_interests,id'
            ],

            // Valida las coordenadas de geolocalización.
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            // Valida el gimnasio seleccionado
            'gym_ids' => ['nullable', 'array'],
            'gym_ids.*' => ['integer', 'exists:gyms,id'],

            // --- Foto de Perfil ---
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:5120'], // 5MB máx
            'remove_profile_picture' => ['nullable', 'boolean'],
            
            // --- Banner de Perfil ---
            'banner_picture' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:10240'], // 10MB máx
            'banner_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'], // Validar formato hexadecimal
        ];
    }
}