<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    workout: Object,
    title: String,
    personalRecords: Object
});

const { success, error, info } = useToast();

const setTypeLabels = {
    normal: 'Normal',
    warmup: 'Calentamiento',
    drop_set: 'Drop Set',
    failure: 'Fallo'
};

// Estado del entrenamiento
const currentExerciseIndex = ref(0);
const completedSets = ref({});
const isResting = ref(false);
const restTimeRemaining = ref(0);
const restInterval = ref(null);
const startTime = ref(Date.now());

// Inicializar completedSets
props.workout.exercises.forEach((exercise, exIndex) => {
    completedSets.value[exIndex] = {};
    exercise.sets_data.forEach((set, setIndex) => {
        completedSets.value[exIndex][setIndex] = false;
    });
});

const currentExercise = computed(() => props.workout.exercises[currentExerciseIndex.value]);

const totalSets = computed(() => {
    return props.workout.exercises.reduce((total, ex) => total + ex.sets_data.length, 0);
});

const completedSetsCount = computed(() => {
    let count = 0;
    Object.values(completedSets.value).forEach(exercise => {
        Object.values(exercise).forEach(completed => {
            if (completed) count++;
        });
    });
    return count;
});

const progressPercentage = computed(() => {
    return Math.round((completedSetsCount.value / totalSets.value) * 100);
});

const allSetsCompleted = computed(() => {
    return completedSetsCount.value === totalSets.value;
});

function toggleSet(exerciseIndex, setIndex) {
    const wasCompleted = completedSets.value[exerciseIndex][setIndex];
    completedSets.value[exerciseIndex][setIndex] = !wasCompleted;
    
    // Si se marcó como completada
    if (!wasCompleted) {
        // Verificar PR
        const exercise = props.workout.exercises[exerciseIndex];
        const set = exercise.sets_data[setIndex];
        const exerciseName = exercise.exercise_name.toLowerCase();
        const pr = props.personalRecords?.[exerciseName];
        
        if (pr) {
            if (set.weight > pr.max_weight) {
                info(`🏆 ¡Nuevo Récord de Peso! ${set.weight}kg en ${exercise.exercise_name}`);
                // Actualizar PR localmente para no spammear
                pr.max_weight = set.weight;
            } else if (set.weight === pr.max_weight && set.reps > pr.max_reps) {
                info(`🏆 ¡Nuevo Récord de Reps! ${set.reps} reps con ${set.weight}kg`);
                pr.max_reps = set.reps;
            }
        }

        // Iniciar descanso
        if (currentExercise.value.rest_seconds) {
            startRest(currentExercise.value.rest_seconds);
        }
    }
}

function startRest(seconds) {
    if (restInterval.value) {
        clearInterval(restInterval.value);
    }
    
    isResting.value = true;
    restTimeRemaining.value = seconds;
    
    restInterval.value = setInterval(() => {
        restTimeRemaining.value--;
        if (restTimeRemaining.value <= 0) {
            clearInterval(restInterval.value);
            isResting.value = false;
            // Reproducir sonido o vibración (opcional)
        }
    }, 1000);
}

function skipRest() {
    if (restInterval.value) {
        clearInterval(restInterval.value);
    }
    isResting.value = false;
    restTimeRemaining.value = 0;
}

function nextExercise() {
    if (currentExerciseIndex.value < props.workout.exercises.length - 1) {
        currentExerciseIndex.value++;
    }
}

function previousExercise() {
    if (currentExerciseIndex.value > 0) {
        currentExerciseIndex.value--;
    }
}

const showFinishModal = ref(false);
const workoutNotes = ref('');
const isSubmitting = ref(false);

function openFinishModal() {
    showFinishModal.value = true;
}

function confirmFinish() {
    if (isSubmitting.value) return;
    isSubmitting.value = true;
    
    const duration = Math.round((Date.now() - startTime.value) / 1000 / 60); // minutos
    
    // Preparar datos del log
    const exercisesData = props.workout.exercises.map((exercise, exIndex) => ({
        name: exercise.exercise_name,
        sets: exercise.sets_data.map((set, setIndex) => ({
            ...set,
            completed: completedSets.value[exIndex][setIndex]
        }))
    }));
    
    // Guardar el log
    router.post(route('workout-logs.store'), {
        workout_id: props.workout.id,
        workout_name: props.workout.name,
        exercises_data: exercisesData,
        duration_minutes: duration,
        total_sets: totalSets.value,
        completed_sets: completedSetsCount.value,
        notes: workoutNotes.value
    }, {
        onSuccess: () => {
            success(`¡Entrenamiento completado! Duración: ${duration} minutos.`);
            showFinishModal.value = false;
        },
        onError: () => {
            error('Error al guardar el registro del entrenamiento');
            isSubmitting.value = false;
        },
        onFinish: () => {
            // No reseteamos isSubmitting aquí si es exitoso porque redirige
        }
    });
}

function exitWorkout() {
    if (!confirm('¿Seguro que quieres salir? Se perderá el progreso actual.')) return;
    router.visit(route('workouts.show', props.workout.id));
}

// Limpiar intervalo al desmontar
watch(() => {}, () => {
    if (restInterval.value) {
        clearInterval(restInterval.value);
    }
});
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-purple-50 pb-24">
            
            <!-- Header fijo -->
            <div class="sticky top-16 z-20 bg-white shadow-md border-b border-gray-200">
                <div class="max-w-4xl mx-auto px-4 py-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ workout.name }}</h1>
                            <p class="text-sm text-gray-500">Ejercicio {{ currentExerciseIndex + 1 }} de {{ workout.exercises.length }}</p>
                        </div>
                        <button 
                            @click="exitWorkout"
                            class="p-2 text-gray-500 hover:text-red-600 transition"
                        >
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Barra de progreso -->
                    <div class="relative">
                        <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                            <div 
                                class="h-full bg-gradient-to-r from-indigo-600 to-purple-600 transition-all duration-500"
                                :style="{ width: progressPercentage + '%' }"
                            ></div>
                        </div>
                        <p class="text-xs text-center text-gray-600 mt-1 font-bold">
                            {{ completedSetsCount }} / {{ totalSets }} series ({{ progressPercentage }}%)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Cronómetro de descanso (modal flotante) -->
            <div 
                v-if="isResting" 
                class="fixed inset-0 bg-black/50 z-30 flex items-center justify-center backdrop-blur-sm"
            >
                <div class="bg-white rounded-3xl p-8 shadow-2xl text-center max-w-sm mx-4 animate-pulse">
                    <div class="text-6xl mb-4">⏱️</div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Descansa</h3>
                    <div class="text-7xl font-black text-indigo-600 mb-6">
                        {{ restTimeRemaining }}
                    </div>
                    <button 
                        @click="skipRest"
                        class="px-6 py-3 bg-gray-200 hover:bg-gray-300 rounded-xl font-bold transition"
                    >
                        Saltar descanso
                    </button>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="max-w-4xl mx-auto px-4 py-8">
                
                <!-- Ejercicio actual -->
                <div class="bg-white rounded-3xl shadow-xl p-6 mb-6 border-4 border-indigo-200">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-3xl font-black text-gray-900">
                            {{ currentExercise.exercise_name }}
                        </h2>
                        <span class="text-5xl">💪</span>
                    </div>
                    
                    <p v-if="currentExercise.notes" class="text-gray-600 mb-4 italic">
                        📝 {{ currentExercise.notes }}
                    </p>

                    <!-- Mostrar PR actual -->
                    <div 
                        v-if="personalRecords?.[currentExercise.exercise_name.toLowerCase()]?.max_weight > 0"
                        class="mb-6 p-3 bg-yellow-50 border border-yellow-200 rounded-xl flex items-center gap-3"
                    >
                        <span class="text-2xl">🏆</span>
                        <div>
                            <p class="text-xs font-bold text-yellow-800 uppercase tracking-wider">Tu Récord Actual</p>
                            <p class="text-sm text-yellow-900">
                                <span class="font-black">{{ personalRecords[currentExercise.exercise_name.toLowerCase()].max_weight }}kg</span> 
                                (máx peso) • 
                                <span class="font-black">{{ personalRecords[currentExercise.exercise_name.toLowerCase()].max_reps }} reps</span> 
                                (máx reps)
                            </p>
                        </div>
                    </div>

                    <!-- Series -->
                    <div class="space-y-3">
                        <div 
                            v-for="(set, setIndex) in currentExercise.sets_data" 
                            :key="setIndex"
                            @click="toggleSet(currentExerciseIndex, setIndex)"
                            class="flex items-center gap-4 p-4 rounded-2xl border-2 transition-all cursor-pointer"
                            :class="completedSets[currentExerciseIndex][setIndex] 
                                ? 'bg-green-50 border-green-400' 
                                : 'bg-gray-50 border-gray-200 hover:border-indigo-300'"
                        >
                            <!-- Checkbox -->
                            <div class="flex-shrink-0">
                                <div 
                                    class="w-8 h-8 rounded-full border-2 flex items-center justify-center transition-all"
                                    :class="completedSets[currentExerciseIndex][setIndex] 
                                        ? 'bg-green-500 border-green-500' 
                                        : 'border-gray-300'"
                                >
                                    <svg v-if="completedSets[currentExerciseIndex][setIndex]" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Info de la serie (Rediseño Vertical) -->
                            <div class="flex-1 flex flex-col items-center justify-center gap-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-gray-500 uppercase tracking-wider">Serie {{ setIndex + 1 }}</span>
                                    <span 
                                        v-if="set.type && set.type !== 'normal'"
                                        class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-700"
                                    >
                                        {{ setTypeLabels[set.type] || set.type }}
                                    </span>
                                </div>
                                
                                <div class="flex items-center gap-3" @click.stop>
                                    <!-- Reps -->
                                    <div class="flex flex-col items-center">
                                        <div class="flex items-center bg-gray-50 rounded-lg px-2 py-1 border border-gray-100">
                                            <input 
                                                v-model="set.reps"
                                                type="number"
                                                class="w-10 bg-transparent border-none p-0 text-center font-black text-indigo-600 text-xl focus:ring-0 appearance-none"
                                                min="0"
                                                placeholder="0"
                                            />
                                        </div>
                                        <span class="text-[10px] text-gray-400 font-bold uppercase mt-0.5">Reps</span>
                                    </div>

                                    <span class="text-gray-300 text-xl font-light">/</span>

                                    <!-- Peso -->
                                    <div class="flex flex-col items-center">
                                        <div class="flex items-center bg-gray-50 rounded-lg px-2 py-1 border border-gray-100">
                                            <input 
                                                v-model="set.weight"
                                                type="number"
                                                class="w-14 bg-transparent border-none p-0 text-center font-black text-purple-600 text-xl focus:ring-0 appearance-none"
                                                min="0"
                                                step="0.5"
                                                placeholder="0"
                                            />
                                        </div>
                                        <span class="text-[10px] text-gray-400 font-bold uppercase mt-0.5">Kg</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navegación entre ejercicios -->
                    <div class="flex gap-3 mt-6">
                        <button 
                            @click="previousExercise"
                            :disabled="currentExerciseIndex === 0"
                            class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 rounded-xl font-bold transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            ← Anterior
                        </button>
                        <button 
                            @click="nextExercise"
                            :disabled="currentExerciseIndex === workout.exercises.length - 1"
                            class="flex-1 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Siguiente →
                        </button>
                    </div>
                </div>

                <!-- Botón finalizar -->
                <button 
                    @click="openFinishModal"
                    class="w-full py-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-2xl font-black text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all"
                    :class="allSetsCompleted ? 'animate-pulse' : ''"
                >
                    {{ allSetsCompleted ? '🎉 ¡Finalizar Entrenamiento!' : 'Finalizar Entrenamiento' }}
                </button>

            </div>
        </div>

        <!-- Modal de Finalización con Notas -->
        <div v-if="showFinishModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
            <div class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl animate-fadeIn">
                <div class="text-center mb-6">
                    <div class="text-6xl mb-2">🎉</div>
                    <h2 class="text-2xl font-black text-gray-900">¡Entrenamiento Terminado!</h2>
                    <p class="text-gray-500">Has completado {{ completedSetsCount }} de {{ totalSets }} series.</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Notas del entrenamiento (opcional)</label>
                    <textarea 
                        v-model="workoutNotes"
                        rows="4"
                        class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="¿Cómo te sentiste? ¿Algún dolor? ¿Récord personal?"
                    ></textarea>
                </div>

                <div class="flex gap-3">
                    <button 
                        @click="showFinishModal = false"
                        class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition"
                    >
                        Cancelar
                    </button>
                    <button 
                        @click="confirmFinish"
                        class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg"
                        :disabled="isSubmitting"
                    >
                        {{ isSubmitting ? 'Guardando...' : 'Guardar Log' }}
                    </button>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<style scoped>
.animate-fadeIn {
    animation: fadeIn 0.2s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
</style>
