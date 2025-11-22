<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from '@/composables/useToast';

const { success, error } = useToast();

defineProps({
    title: String
});

const form = ref({
    name: '',
    description: '',
    difficulty_level: 'intermedio',
    duration_minutes: null,
    category: 'Gym',
    is_public: true,
    exercises: [
        { exercise_name: '', sets: 3, reps: '10', weight_kg: null, rest_seconds: 60, notes: '' }
    ]
});

const isSubmitting = ref(false);

const categories = [
    '🏋️ Gym', 
    '🤸 Calistenia', 
    '🏃 Running', 
    '🚴 Ciclismo', 
    '🧘 Yoga',
    '⚽ Deportes de equipo',
    '🥊 Artes Marciales',
    '🏊 Natación',
    '💪 Otro'
];

const addExercise = () => {
    form.value.exercises.push({
        exercise_name: '',
        sets: 3,
        reps: '10',
        weight_kg: null,
        rest_seconds: 60,
        notes: ''
    });
};

const removeExercise = (index) => {
    if (form.value.exercises.length > 1) {
        form.value.exercises.splice(index, 1);
    }
};

const submit = () => {
    // Validación básica
    if (!form.value.name.trim()) {
        error('El nombre de la rutina es obligatorio');
        return;
    }

    if (form.value.exercises.some(ex => !ex.exercise_name.trim())) {
        error('Todos los ejercicios deben tener un nombre');
        return;
    }

    isSubmitting.value = true;
    
    router.post(route('workouts.store'), form.value, {
        onSuccess: () => {
            success('¡Rutina creada exitosamente!');
        },
        onError: (errors) => {
            console.error(errors);
            error('Error al crear la rutina');
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};
</script>

<template>
    <Head :title="title" />
    
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-md">
                    <span class="text-2xl">🏋️</span>
                </div>
                <div>
                    <h2 class="font-extrabold text-xl text-gray-900 leading-tight">
                        Crear Rutina
                    </h2>
                    <p class="text-xs text-gray-500 font-medium">Comparte tu rutina con la comunidad</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-3xl mx-auto px-4">
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <!-- Card Principal -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Información Básica</h3>
                        
                        <!-- Nombre -->
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Nombre de la Rutina *
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Ej: Rutina Push-Pull-Legs"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 outline-none"
                                required
                            />
                        </div>

                        <!-- Descripción -->
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Descripción
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="Describe tu rutina..."
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 outline-none resize-none"
                            ></textarea>
                        </div>

                        <!-- Grid de 3 columnas -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Categoría -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    Categoría *
                                </label>
                                <select
                                    v-model="form.category"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 outline-none"
                                    required
                                >
                                    <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                                </select>
                            </div>

                            <!-- Dificultad -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    Dificultad *
                                </label>
                                <select
                                    v-model="form.difficulty_level"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 outline-none"
                                    required
                                >
                                    <option value="principiante">Principiante</option>
                                    <option value="intermedio">Intermedio</option>
                                    <option value="avanzado">Avanzado</option>
                                </select>
                            </div>

                            <!-- Duración -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    Duración (min)
                                </label>
                                <input
                                    v-model.number="form.duration_minutes"
                                    type="number"
                                    min="1"
                                    placeholder="60"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 outline-none"
                                />
                            </div>
                        </div>

                        <!-- Público/Privado -->
                        <div class="mt-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    v-model="form.is_public"
                                    type="checkbox"
                                    class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500"
                                />
                                <span class="text-sm font-medium text-gray-700">Hacer pública esta rutina</span>
                            </label>
                        </div>
                    </div>

                    <!-- Ejercicios -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Ejercicios</h3>
                            <button
                                type="button"
                                @click="addExercise"
                                class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg font-semibold text-sm hover:bg-indigo-200 transition"
                            >
                                + Agregar Ejercicio
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="(exercise, index) in form.exercises"
                                :key="index"
                                class="p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-300 transition"
                            >
                                <div class="flex items-start justify-between mb-3">
                                    <span class="inline-flex items-center justify-center w-8 h-8 bg-indigo-100 text-indigo-700 rounded-full font-bold text-sm">
                                        {{ index + 1 }}
                                    </span>
                                    <button
                                        v-if="form.exercises.length > 1"
                                        type="button"
                                        @click="removeExercise(index)"
                                        class="text-red-500 hover:text-red-700 text-sm font-semibold"
                                    >
                                        Eliminar
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <!-- Nombre del ejercicio -->
                                    <div class="md:col-span-2">
                                        <input
                                            v-model="exercise.exercise_name"
                                            type="text"
                                            placeholder="Nombre del ejercicio *"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 outline-none"
                                            required
                                        />
                                    </div>

                                    <!-- Series -->
                                    <div>
                                        <input
                                            v-model.number="exercise.sets"
                                            type="number"
                                            min="1"
                                            placeholder="Series"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 outline-none"
                                        />
                                    </div>

                                    <!-- Reps -->
                                    <div>
                                        <input
                                            v-model="exercise.reps"
                                            type="text"
                                            placeholder="Reps (ej: 10-12)"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 outline-none"
                                        />
                                    </div>

                                    <!-- Peso -->
                                    <div>
                                        <input
                                            v-model.number="exercise.weight_kg"
                                            type="number"
                                            min="0"
                                            step="0.5"
                                            placeholder="Peso (kg)"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 outline-none"
                                        />
                                    </div>

                                    <!-- Descanso -->
                                    <div>
                                        <input
                                            v-model.number="exercise.rest_seconds"
                                            type="number"
                                            min="0"
                                            placeholder="Descanso (seg)"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 outline-none"
                                        />
                                    </div>

                                    <!-- Notas -->
                                    <div class="md:col-span-2">
                                        <textarea
                                            v-model="exercise.notes"
                                            rows="2"
                                            placeholder="Notas (opcional)"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 outline-none resize-none"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="router.visit(route('profile.show.public', { user: $page.props.auth.user.username }))"
                            class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 rounded-xl font-bold transition"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 transition shadow-lg"
                        >
                            {{ isSubmitting ? 'Creando...' : '✨ Crear Rutina' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
