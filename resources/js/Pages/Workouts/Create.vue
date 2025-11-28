<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import SelectInput from '@/Components/SelectInput.vue';
import { useToast } from '@/composables/useToast';

const { success, error } = useToast();

defineProps({
    title: String
});

const form = ref({
    name: '',
    description: '',
    difficulty: 'Intermedio',
    duration_minutes: null,
    category: 'Gym',
    is_public: true,
    exercises: [
        { 
            exercise_name: '', 
            sets_data: [{ reps: 10, weight: 0, type: 'normal' }], // Inicializamos con 1 serie
            rest_seconds: 60, 
            notes: '' 
        }
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

const categoryOptions = categories.map(cat => ({ value: cat, label: cat }));

const difficultyOptions = [
    { value: 'Principiante', label: 'Principiante' },
    { value: 'Intermedio', label: 'Intermedio' },
    { value: 'Avanzado', label: 'Avanzado' }
];

const setTypes = [
    { value: 'normal', label: 'Normal' },
    { value: 'warmup', label: 'Calentamiento' },
    { value: 'failure', label: 'Al fallo' },
    { value: 'drop', label: 'Drop Set' }
];

const addExercise = () => {
    form.value.exercises.push({
        exercise_name: '',
        sets_data: [{ reps: 10, weight: 0, type: 'normal' }],
        rest_seconds: 60,
        notes: ''
    });
};

const removeExercise = (index) => {
    if (form.value.exercises.length > 1) {
        form.value.exercises.splice(index, 1);
    }
};

const addSet = (exerciseIndex) => {
    const previousSet = form.value.exercises[exerciseIndex].sets_data[form.value.exercises[exerciseIndex].sets_data.length - 1];
    form.value.exercises[exerciseIndex].sets_data.push({
        reps: previousSet ? previousSet.reps : 10,
        weight: previousSet ? previousSet.weight : 0,
        type: 'normal'
    });
};

const removeSet = (exerciseIndex, setIndex) => {
    if (form.value.exercises[exerciseIndex].sets_data.length > 1) {
        form.value.exercises[exerciseIndex].sets_data.splice(setIndex, 1);
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
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-md">
                        <span class="text-2xl">🏋️</span>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-xl text-gray-900 dark:text-white leading-tight transition-colors">
                            Crear Rutina
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium transition-colors">Diseña tu entrenamiento serie a serie</p>
                    </div>
                </div>
                <Link 
                    :href="route('workouts.my-workouts')"
                    class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg font-bold text-sm shadow-sm border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                >
                    Volver
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-3xl mx-auto px-4">
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <!-- Card Principal -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 transition-colors">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 transition-colors">Información Básica</h3>
                        
                        <!-- Nombre -->
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 transition-colors">
                                Nombre de la Rutina *
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Ej: Rutina Push-Pull-Legs"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-900 outline-none transition-colors"
                                required
                            />
                        </div>

                        <!-- Descripción -->
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 transition-colors">
                                Descripción
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="Describe tu rutina..."
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-900 outline-none resize-none transition-colors"
                            ></textarea>
                        </div>

                        <!-- Grid de 3 columnas -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Categoría -->
                            <div>
                                <SelectInput
                                    id="category"
                                    label="Categoría *"
                                    v-model="form.category"
                                    :options="categoryOptions"
                                    required
                                />
                            </div>

                            <!-- Dificultad -->
                            <div>
                                <SelectInput
                                    id="difficulty"
                                    label="Dificultad *"
                                    v-model="form.difficulty"
                                    :options="difficultyOptions"
                                    required
                                />
                            </div>

                            <!-- Duración -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 transition-colors">
                                    Duración (min)
                                </label>
                                <input
                                    v-model.number="form.duration_minutes"
                                    type="number"
                                    min="1"
                                    placeholder="60"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-900 outline-none transition-colors"
                                />
                            </div>
                        </div>

                        <!-- Público/Privado -->
                        <div class="mt-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    v-model="form.is_public"
                                    type="checkbox"
                                    class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 transition-colors"
                                />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 transition-colors">Hacer pública esta rutina</span>
                            </label>
                        </div>
                    </div>

                    <!-- Ejercicios -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 transition-colors">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white transition-colors">Ejercicios</h3>
                            <button
                                type="button"
                                @click="addExercise"
                                class="px-4 py-2 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 rounded-lg font-semibold text-sm hover:bg-indigo-200 dark:hover:bg-indigo-900/50 transition-colors"
                            >
                                + Agregar Ejercicio
                            </button>
                        </div>

                        <div class="space-y-6">
                            <div
                                v-for="(exercise, index) in form.exercises"
                                :key="index"
                                class="p-5 border-2 border-gray-200 dark:border-gray-700 rounded-xl hover:border-indigo-300 dark:hover:border-indigo-500 transition-colors bg-gray-50/50 dark:bg-gray-700/50"
                            >
                                <!-- Cabecera Ejercicio -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3 flex-1">
                                        <span class="inline-flex items-center justify-center w-8 h-8 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-full font-bold text-sm flex-shrink-0 transition-colors">
                                            {{ index + 1 }}
                                        </span>
                                        <input
                                            v-model="exercise.exercise_name"
                                            type="text"
                                            placeholder="Nombre del ejercicio *"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-900 outline-none font-bold text-gray-800 dark:text-white bg-white dark:bg-gray-800 transition-colors"
                                            required
                                        />
                                    </div>
                                    <button
                                        v-if="form.exercises.length > 1"
                                        type="button"
                                        @click="removeExercise(index)"
                                        class="text-red-500 hover:text-red-700 text-sm font-semibold ml-3"
                                    >
                                        Eliminar
                                    </button>
                                </div>

                                <!-- Lista de Series -->
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase transition-colors">Series</label>
                                        <button 
                                            type="button" 
                                            @click="addSet(index)"
                                            class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors"
                                        >
                                            + Añadir Serie
                                        </button>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <div 
                                            v-for="(set, setIndex) in exercise.sets_data" 
                                            :key="setIndex"
                                            class="flex items-center gap-2"
                                        >
                                            <span class="text-xs text-gray-400 w-4 text-center">{{ setIndex + 1 }}</span>
                                            
                                            <div class="flex-1 grid grid-cols-3 gap-2">
                                                <div class="relative">
                                                    <input
                                                        v-model.number="set.reps"
                                                        type="number"
                                                        placeholder="Reps"
                                                        class="w-full px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-indigo-500 dark:focus:border-indigo-500 outline-none transition-colors"
                                                    />
                                                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">reps</span>
                                                </div>
                                                <div class="relative">
                                                    <input
                                                        v-model.number="set.weight"
                                                        type="number"
                                                        placeholder="Kg"
                                                        class="w-full px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-indigo-500 dark:focus:border-indigo-500 outline-none transition-colors"
                                                    />
                                                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">kg</span>
                                                </div>
                                                <select
                                                    v-model="set.type"
                                                    class="w-full px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-indigo-500 dark:focus:border-indigo-500 outline-none transition-colors"
                                                >
                                                    <option v-for="type in setTypes" :key="type.value" :value="type.value">
                                                        {{ type.label }}
                                                    </option>
                                                </select>
                                            </div>

                                            <button 
                                                v-if="exercise.sets_data.length > 1"
                                                type="button"
                                                @click="removeSet(index, setIndex)"
                                                class="text-gray-400 hover:text-red-500"
                                            >
                                                &times;
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Opciones Extra (Descanso y Notas) -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-3 border-t border-gray-200 dark:border-gray-600 transition-colors">
                                    <div>
                                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase ml-1 transition-colors">Descanso (s)</label>
                                        <input
                                            v-model.number="exercise.rest_seconds"
                                            type="number"
                                            min="0"
                                            placeholder="60"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-900 outline-none transition-colors"
                                        />
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase ml-1 transition-colors">Notas</label>
                                        <input
                                            v-model="exercise.notes"
                                            type="text"
                                            placeholder="Notas opcionales..."
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-900 outline-none transition-colors"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-3 pb-8">
                        <button
                            type="button"
                            @click="router.visit(route('workouts.my-workouts'))"
                            class="flex-1 px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-xl font-bold transition-colors"
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
