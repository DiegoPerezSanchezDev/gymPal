<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import SelectInput from '@/Components/SelectInput.vue';
import SetTypeDropdown from '@/Components/SetTypeDropdown.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    title: String,
    workout: Object,
    categories: Array
});

const { success, error } = useToast();

const form = ref({
    name: '',
    description: '',
    difficulty: 'Intermedio',
    duration_minutes: null,
    category_id: null,
    is_public: true,
    exercises: []
});

const isSubmitting = ref(false);
const setCounter = ref(1);

const categoryOptions = props.categories?.map(cat => ({ 
    value: cat.id, 
    label: `${cat.icon} ${cat.name}` 
})) || [];

const difficultyOptions = [
    { value: 'Principiante', label: 'Principiante' },
    { value: 'Intermedio', label: 'Intermedio' },
    { value: 'Avanzado', label: 'Avanzado' }
];

const setTypes = [
    { value: 'normal', label: 'Normal' },
    { value: 'warmup', label: 'Calentamiento' },
    { value: 'failure', label: 'Al Fallo' },
    { value: 'drop', label: 'Drop Set' }
];

// Initialize form with existing data
onMounted(() => {
    if (props.workout) {
        form.value.name = props.workout.name;
        form.value.description = props.workout.description;
        form.value.difficulty = props.workout.difficulty;
        form.value.duration_minutes = props.workout.duration_minutes;
        form.value.category_id = props.workout.category_id;
        form.value.is_public = !!props.workout.is_public; // Ensure boolean
        
        // Map exercises
        if (props.workout.exercises && props.workout.exercises.length > 0) {
            form.value.exercises = props.workout.exercises.map(ex => ({
                exercise_name: ex.exercise_name,
                sets_data: (Array.isArray(ex.sets_data) ? ex.sets_data : [{ reps: 10, weight: 0, type: 'normal' }]).map(s => ({
                    ...s,
                    id: setCounter.value++
                })),
                rest_seconds: ex.rest_seconds,
                notes: ex.notes
            }));
        } else {
            addExercise(); // Ensure at least one
        }
    }
});

const addExercise = () => {
    form.value.exercises.push({
        exercise_name: '',
        sets_data: [{ id: setCounter.value++, reps: 10, weight: 0, type: 'normal' }],
        rest_seconds: 60,
        notes: ''
    });
};

const addDropSet = (exerciseIndex, setIndex) => {
    const parentSet = form.value.exercises[exerciseIndex].sets_data[setIndex];
    // Insertar justo debajo de la serie actual
    form.value.exercises[exerciseIndex].sets_data.splice(setIndex + 1, 0, {
        id: setCounter.value++,
        reps: parentSet.reps != null ? Number(parentSet.reps) : 10,
        weight: parentSet.weight != null ? Math.round(Number(parentSet.weight) * 0.7) : 0,
        type: 'drop'
    });
};

const handleTypeChange = (val, exerciseIndex, setIndex) => {
    if (val === 'drop') {
        addDropSet(exerciseIndex, setIndex);
    }
};

const isSubsequentDrop = (exercise, setIndex) => {
    return setIndex > 0 && exercise.sets_data[setIndex].type === 'drop' && exercise.sets_data[setIndex - 1].type === 'drop';
};

const showDeleteConfirm = ref(false);
const exerciseToDeleteIndex = ref(null);

const confirmRemoveExercise = (index) => {
    if (form.value.exercises.length > 1) {
        exerciseToDeleteIndex.value = index;
        showDeleteConfirm.value = true;
    }
};

const handleRemoveExercise = () => {
    if (exerciseToDeleteIndex.value !== null) {
        form.value.exercises.splice(exerciseToDeleteIndex.value, 1);
        showDeleteConfirm.value = false;
        exerciseToDeleteIndex.value = null;
        success('Ejercicio eliminado');
    }
};

const addSet = (exerciseIndex) => {
    const previousSet = form.value.exercises[exerciseIndex].sets_data[form.value.exercises[exerciseIndex].sets_data.length - 1];
    form.value.exercises[exerciseIndex].sets_data.push({
        id: setCounter.value++,
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
    
    router.put(route('workouts.update', props.workout.id), form.value, {
        onSuccess: () => {
            success('¡Rutina actualizada exitosamente!');
        },
        onError: (errors) => {
            console.error(errors);
            error('Error al actualizar la rutina');
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
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-md text-white">
                        <span class="text-2xl">✏️</span>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-xl text-gray-900 dark:text-white leading-tight transition-colors">
                            Editar Rutina
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium transition-colors">Modifica los detalles de tu entrenamiento</p>
                    </div>
                </div>
                <Link 
                    :href="route('workouts.show', workout.id)"
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
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 outline-none transition-colors"
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
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 outline-none resize-none transition-colors"
                            ></textarea>
                        </div>

                        <!-- Grid de 3 columnas -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Categoría -->
                            <div>
                                <SelectInput
                                    id="category"
                                    label="Categoría *"
                                    v-model="form.category_id"
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
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 outline-none transition-colors"
                                />
                            </div>
                        </div>

                        <!-- Público/Privado (solo si no es una rutina clonada) -->
                        <div v-if="!workout.original_workout_id" class="mt-4">
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
                                class="px-4 py-2 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-lg font-semibold text-sm hover:bg-indigo-200 dark:hover:bg-indigo-900/50 transition-colors"
                            >
                                + Agregar Ejercicio
                            </button>
                        </div>

                        <div class="space-y-6">
                            <div
                                v-for="(exercise, index) in form.exercises"
                                :key="index"
                                class="p-5 border-2 border-gray-200 dark:border-gray-700 rounded-xl hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors bg-gray-50/50 dark:bg-gray-700/50"
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
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-gray-800 dark:text-white bg-white dark:bg-gray-700 transition-colors"
                                            required
                                        />
                                    </div>
                                    <button
                                        v-if="form.exercises.length > 1"
                                        type="button"
                                        @click="confirmRemoveExercise(index)"
                                        class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-sm font-semibold ml-3 transition-colors"
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
                                            :key="set.id"
                                            class="flex items-center gap-2 p-1 rounded-lg transition-all border shadow-sm group/row"
                                            :class="{
                                                'bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700': set.type === 'normal' || !set.type,
                                                'bg-orange-50/50 dark:bg-orange-900/10 border-orange-100 dark:border-orange-900/30': set.type === 'warmup',
                                                'bg-red-50/50 dark:bg-red-900/10 border-red-100 dark:border-red-900/30': set.type === 'failure',
                                                'bg-purple-50/50 dark:bg-purple-900/10 border-purple-100 dark:border-purple-900/30 ml-2': set.type === 'drop',
                                            }"
                                        >
                                            <div class="flex items-center justify-center w-5 flex-shrink-0">
                                                <span class="text-[10px] text-gray-400 font-black">{{ setIndex + 1 }}</span>
                                            </div>
                                            
                                            <div class="flex-1 grid gap-2" :class="isSubsequentDrop(exercise, setIndex) ? 'grid-cols-2' : 'grid-cols-3'">
                                                <div class="relative">
                                                    <input
                                                        v-model.number="set.reps"
                                                        type="number"
                                                        :placeholder="set.type === 'failure' ? 'MAX' : 'Reps'"
                                                        class="w-full px-2 py-1.5 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-indigo-500 dark:focus:border-indigo-500 outline-none transition-all"
                                                    />
                                                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[9px] text-gray-400 pointer-events-none uppercase font-bold">reps</span>
                                                </div>
                                                <div class="relative">
                                                    <input
                                                        v-model.number="set.weight"
                                                        type="number"
                                                        placeholder="Kg"
                                                        class="w-full px-2 py-1.5 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-indigo-500 dark:focus:border-indigo-500 outline-none transition-all"
                                                    />
                                                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[9px] text-gray-400 pointer-events-none uppercase font-bold">kg</span>
                                                </div>
                                                <SetTypeDropdown
                                                    v-if="!isSubsequentDrop(exercise, setIndex)"
                                                    v-model="set.type"
                                                    :options="setTypes"
                                                    @update:model-value="(val) => handleTypeChange(val, index, setIndex)"
                                                />
                                            </div>
 
                                            <div class="flex items-center gap-1 flex-shrink-0">
                                                <button 
                                                    v-if="isSubsequentDrop(exercise, setIndex)"
                                                    type="button"
                                                    @click="addDropSet(index, setIndex)"
                                                    class="w-7 h-7 flex items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-600 hover:bg-purple-200 dark:hover:bg-purple-800/50 transition-all border border-purple-200 dark:border-purple-800"
                                                    title="Agregar otra bajada"
                                                >
                                                    <span class="text-[10px]">⬇️</span>
                                                </button>
 
                                                <button 
                                                    v-if="exercise.sets_data.length > 1"
                                                    type="button"
                                                    @click="removeSet(index, setIndex)"
                                                    class="w-7 h-7 flex items-center justify-center rounded-lg bg-red-50 dark:bg-red-900/20 text-gray-400 hover:text-red-500 hover:bg-red-100 dark:hover:bg-red-900/40 transition-all border border-transparent hover:border-red-200 dark:hover:border-red-800"
                                                >
                                                    &times;
                                                </button>
                                                <div v-else class="w-7 h-7"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Opciones Extra (Descanso y Notas) -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-3 border-t border-gray-200 dark:border-gray-700 transition-colors">
                                    <div>
                                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase ml-1 transition-colors">Descanso (s)</label>
                                        <input
                                            v-model.number="exercise.rest_seconds"
                                            type="number"
                                            min="0"
                                            placeholder="60"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-colors"
                                        />
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase ml-1 transition-colors">Notas</label>
                                        <input
                                            v-model="exercise.notes"
                                            type="text"
                                            placeholder="Notas opcionales..."
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-colors"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex flex-col sm:flex-row gap-4 pb-12 sm:justify-center">
                        <button
                            type="button"
                            @click="router.visit(route('workouts.show', workout.id))"
                            class="w-full sm:w-48 px-8 py-4 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-2xl font-bold hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="w-full sm:w-64 group relative px-8 py-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-3 disabled:opacity-70 disabled:cursor-not-allowed"
                        >
                            <svg v-if="!isSubmitting" class="w-6 h-6 group-hover:rotate-12 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <svg v-else class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ isSubmitting ? 'Guardando...' : 'Guardar Cambios' }}</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
        <ConfirmModal
            :show="showDeleteConfirm"
            title="¿Eliminar ejercicio?"
            message="Esta acción no se puede deshacer. Se eliminarán todas las series configuradas para este ejercicio."
            confirmText="Eliminar Ejercicio"
            type="danger"
            @close="showDeleteConfirm = false"
            @confirm="handleRemoveExercise"
        />
    </AuthenticatedLayout>
</template>
