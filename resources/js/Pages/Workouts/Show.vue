<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useToast } from '@/composables/useToast';
import axios from 'axios';

const props = defineProps({
    workout: Object,
    isSaved: Boolean,
    isOwner: Boolean,
    logsCount: Number,
    recentLogs: Array,
    title: String
});

const { success, error } = useToast();
const isSaving = ref(false);
const localIsSaved = ref(props.isSaved);
const localSaveCount = ref(props.workout.times_saved);
const expandedExercises = ref({});
const showDuplicateModal = ref(false);
const duplicateIsPublic = ref(false);
const showDeleteModal = ref(false);

const difficultyColors = {
    Principiante: 'from-emerald-400 to-teal-500',
    Intermedio: 'from-blue-500 to-indigo-600',
    Avanzado: 'from-purple-500 to-pink-600'
};

const difficultyLabel = {
    Principiante: 'Principiante',
    Intermedio: 'Intermedio',
    Avanzado: 'Avanzado'
};

const categoryIcons = {
    'Gym': '🏋️',
    'Calistenia': '🤸',
    'Running': '🏃',
    'Ciclismo': '🚴',
    'Yoga': '🧘',
    'Deportes de equipo': '⚽',
    'Artes Marciales': '🥊',
    'Natación': '🏊',
    'Otro': '💪'
};

// isOwner ya viene como prop, no necesitamos recalcularlo
// const isOwner = computed(() => ...);

const backRoute = computed(() => {
    const params = new URLSearchParams(window.location.search);
    
    // Si viene de notificaciones
    if (params.get('from') === 'notifications') {
        return route('notifications.index');
    }
    
    // Si viene de "Mis Rutinas"
    if (params.get('from') === 'my-workouts' || document.referrer.includes('my-workouts')) {
        return route('workouts.my-workouts');
    }
    
    // Por defecto, ir al perfil del dueño en la tab de rutinas
    return route('profile.show.public', { user: props.workout.user.username }) + '?tab=rutinas';
});

const headerGradient = computed(() => {
    return difficultyColors[props.workout.difficulty] || difficultyColors.Intermedio;
});

const toggleSave = async () => {
    if (isSaving.value) return;
    isSaving.value = true;

    try {
        const response = await axios.post(route('workouts.toggle-save', props.workout.id));
        localIsSaved.value = response.data.is_saved;
        localSaveCount.value = response.data.times_saved;
        success(response.data.message);
    } catch (err) {
        error('Error al guardar la rutina');
        console.error(err);
    } finally {
        isSaving.value = false;
    }
};

const openDeleteModal = () => {
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    showDeleteModal.value = false;
    router.delete(route('workouts.destroy', props.workout.id));
};

const cancelDelete = () => {
    showDeleteModal.value = false;
};

const toggleExercise = (id) => {
    expandedExercises.value[id] = !expandedExercises.value[id];
};

const openDuplicateModal = () => {
    showDuplicateModal.value = true;
    duplicateIsPublic.value = false; // Default: privado
};

const closeDuplicateModal = () => {
    showDuplicateModal.value = false;
};

const confirmDuplicate = () => {
    router.post(route('workouts.duplicate', props.workout.id), {
        is_public: duplicateIsPublic.value
    });
    closeDuplicateModal();
};
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto pb-24">
            
            <!-- Hero Header -->
            <div class="relative overflow-hidden bg-white shadow-xl rounded-b-3xl mb-8">
                <!-- Gradient Background -->
                <div :class="['absolute inset-0 bg-gradient-to-br opacity-90', headerGradient]"></div>
                
                <!-- Pattern Overlay -->
                <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>

                <div class="relative p-8 text-white">
                    <!-- Top Bar -->
                    <div class="flex justify-between items-start mb-6">
                        <Link :href="route('profile.show.public', { user: workout.user.username })" class="flex items-center gap-3 group">
                            <img 
                                :src="workout.user.profile_picture_url ? `/storage/${workout.user.profile_picture_url}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(workout.user.name)}&background=random`" 
                                class="w-10 h-10 rounded-full border-2 border-white/50 group-hover:border-white transition"
                            />
                            <div>
                                <p class="text-xs text-white/80 font-medium uppercase tracking-wider">Creado por</p>
                                <p class="font-bold group-hover:underline">{{ workout.user.name }}</p>
                            </div>
                        </Link>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <Link 
                                :href="backRoute"
                                class="px-3 py-2 bg-white/20 hover:bg-white/30 rounded-full backdrop-blur-sm transition flex items-center gap-2 text-sm font-medium"
                                title="Volver"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                <span class="hidden sm:inline">Volver</span>
                            </Link>
                            <button 
                                v-if="isOwner"
                                @click="$inertia.visit(route('workouts.edit', workout.id))"
                                class="p-2 bg-white/20 hover:bg-white/30 rounded-full backdrop-blur-sm transition"
                                title="Editar"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>
                            <button 
                                v-if="isOwner"
                                @click="deleteWorkout"
                                class="p-2 bg-white/20 hover:bg-red-500/50 rounded-full backdrop-blur-sm transition"
                                title="Eliminar"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Title & Description -->
                    <h1 class="text-4xl font-black mb-2 tracking-tight">{{ workout.name }}</h1>
                    <p class="text-white/90 text-lg max-w-2xl leading-relaxed">{{ workout.description }}</p>

                    <!-- Stats Grid -->
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
                        <!-- Dificultad -->
                        <div class="bg-black/20 backdrop-blur-md rounded-2xl p-4 border border-white/10 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-xl">
                                ⚡
                            </div>
                            <div>
                                <p class="text-[10px] text-white/60 uppercase font-bold tracking-wider">Dificultad</p>
                                <p class="font-bold text-lg leading-none">{{ workout.difficulty }}</p>
                            </div>
                        </div>

                        <!-- Duración -->
                        <div class="bg-black/20 backdrop-blur-md rounded-2xl p-4 border border-white/10 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-xl">
                                ⏱️
                            </div>
                            <div>
                                <p class="text-[10px] text-white/60 uppercase font-bold tracking-wider">Duración</p>
                                <p class="font-bold text-lg leading-none">{{ workout.duration_minutes || '--' }} min</p>
                            </div>
                        </div>

                        <!-- Categoría -->
                        <div class="bg-black/20 backdrop-blur-md rounded-2xl p-4 border border-white/10 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-xl">
                                🏷️
                            </div>
                            <div>
                                <p class="text-[10px] text-white/60 uppercase font-bold tracking-wider">Categoría</p>
                                <p class="font-bold text-lg leading-none">{{ workout.category || 'General' }}</p>
                            </div>
                        </div>

                        <!-- Ejercicios -->
                        <div class="bg-black/20 backdrop-blur-md rounded-2xl p-4 border border-white/10 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-xl">
                                🏋️
                            </div>
                            <div>
                                <p class="text-[10px] text-white/60 uppercase font-bold tracking-wider">Ejercicios</p>
                                <p class="font-bold text-lg leading-none">{{ workout.exercises?.length || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Start Workout Button (prominent) -->
                    <div v-if="isOwner" class="mt-6 flex justify-center">
                        <button 
                            @click="$inertia.visit(route('workouts.live', workout.id))"
                            class="w-full md:w-auto px-12 py-4 rounded-2xl font-black text-lg shadow-2xl transition-all transform hover:scale-105 flex items-center justify-center gap-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white hover:from-green-600 hover:to-emerald-700 border-4 border-white"
                        >
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            ¡Empezar Rutina!
                        </button>
                    </div>

                    <!-- Save & Clone Buttons (if not owner) -->
                    <div v-if="!isOwner" class="mt-6 flex flex-col sm:flex-row justify-center gap-3">
                        <button 
                            @click="toggleSave"
                            class="w-full md:w-auto px-8 py-3 rounded-xl font-bold shadow-lg transition-all transform hover:scale-105 flex items-center justify-center gap-2"
                            :class="localIsSaved ? 'bg-pink-100 text-pink-600 hover:bg-pink-200' : 'bg-white text-gray-700 hover:text-indigo-600 hover:bg-gray-50'"
                        >
                            <svg class="w-6 h-6" :class="localIsSaved ? 'fill-current' : 'fill-none stroke-current'" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                            </svg>
                            {{ localIsSaved ? 'Guardado' : 'Guardar' }}
                        </button>

                        <button 
                            @click="openDuplicateModal"
                            class="w-full md:w-auto px-8 py-3 rounded-xl font-bold shadow-lg transition-all transform hover:scale-105 flex items-center justify-center gap-2 bg-indigo-600 text-white hover:bg-indigo-700 border border-transparent"
                        >
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                            </svg>
                            Clonar y Editar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Exercises List -->
            <div class="px-4">
                <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <span class="bg-indigo-100 text-indigo-600 p-2 rounded-lg">💪</span>
                    Ejercicios
                    <span class="text-sm font-normal text-gray-500 ml-2">({{ workout.exercises.length }} total)</span>
                </h3>

                <div class="relative space-y-6">
                    <!-- Vertical Line -->
                    <div class="absolute left-4 top-4 bottom-4 w-0.5 bg-gray-200"></div>

                    <div 
                        v-for="(exercise, index) in workout.exercises" 
                        :key="exercise.id"
                        class="relative pl-12"
                    >
                        <!-- Number Bubble -->
                        <div 
                            class="absolute left-0 top-0 w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm border-4 border-gray-50 z-10"
                            :class="[
                                'bg-white shadow-sm',
                                index % 2 === 0 ? 'text-indigo-600' : 'text-purple-600'
                            ]"
                        >
                            {{ index + 1 }}
                        </div>

                        <!-- Exercise Card (Clickable) -->
                        <div 
                            @click="toggleExercise(exercise.id)"
                            class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-all cursor-pointer group"
                            :class="{'ring-2 ring-indigo-100': expandedExercises[exercise.id]}"
                        >
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                    {{ exercise.exercise_name }}
                                </h4>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400 bg-gray-100 px-2 py-1 rounded">
                                        {{ exercise.sets_data ? exercise.sets_data.length : 0 }} Series
                                    </span>
                                    <svg 
                                        class="w-5 h-5 text-gray-400 transform transition-transform duration-200"
                                        :class="{'rotate-180': expandedExercises[exercise.id]}"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Resumen rápido (si está colapsado) -->
                            <div v-if="!expandedExercises[exercise.id]" class="text-sm text-gray-500 truncate">
                                <span v-if="exercise.rest_seconds">⏱️ {{ exercise.rest_seconds }}s descanso</span>
                                <span v-if="exercise.notes" class="ml-3">📝 {{ exercise.notes }}</span>
                            </div>

                            <!-- Detalle Expandido -->
                            <div v-if="expandedExercises[exercise.id]" class="mt-4 pt-4 border-t border-gray-100 animate-fadeIn">
                                <div class="space-y-2">
                                    <div 
                                        v-for="(set, setIndex) in exercise.sets_data" 
                                        :key="setIndex"
                                        class="flex items-center justify-between p-2 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors"
                                    >
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs font-bold text-gray-400 w-6">#{{ setIndex + 1 }}</span>
                                            <span class="font-semibold text-gray-800">{{ set.reps }} reps</span>
                                            <span class="text-gray-400">x</span>
                                            <span class="font-semibold text-gray-800">{{ set.weight }} kg</span>
                                        </div>
                                        <span 
                                            v-if="set.type && set.type !== 'normal'"
                                            class="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-indigo-100 text-indigo-700"
                                        >
                                            {{ set.type }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-4 text-sm text-gray-600">
                                    <div v-if="exercise.rest_seconds" class="flex items-center gap-2">
                                        <span class="text-gray-400">⏱️</span>
                                        <span>Descanso: <strong>{{ exercise.rest_seconds }}s</strong></span>
                                    </div>
                                    <div v-if="exercise.notes" class="flex items-center gap-2 col-span-2">
                                        <span class="text-gray-400">📝</span>
                                        <span class="italic">{{ exercise.notes }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="workout.exercises.length === 0" class="text-center py-12 text-gray-500">
                <p>Esta rutina no tiene ejercicios asignados aún.</p>
            </div>

            <!-- Historial de entrenamientos (si hay logs) -->
            <div v-if="logsCount > 0" class="px-4 mt-12">
                <div class="max-w-4xl mx-auto">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-md">
                                <span class="text-xl">📊</span>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-800">Tu Historial</h3>
                                <p class="text-sm text-gray-500">Has completado esta rutina {{ logsCount }} {{ logsCount === 1 ? 'vez' : 'veces' }}</p>
                            </div>
                        </div>
                        <Link 
                            :href="route('workout-logs.index')"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 transition text-sm"
                        >
                            Ver todo
                        </Link>
                    </div>

                    <!-- Últimos 3 logs -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <Link 
                            v-for="log in recentLogs" 
                            :key="log.id"
                            :href="route('workout-logs.show', log.id)"
                            class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all border-2 border-gray-100 hover:border-indigo-200 p-5 group"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs text-gray-500 font-medium">
                                    {{ new Date(log.created_at).toLocaleDateString('es-ES', { day: 'numeric', month: 'short' }) }}
                                </span>
                                <div 
                                    class="w-12 h-12 rounded-full font-black text-sm flex items-center justify-center"
                                    :class="log.completed_sets === log.total_sets 
                                        ? 'bg-green-100 text-green-600' 
                                        : 'bg-yellow-100 text-yellow-600'"
                                >
                                    {{ Math.round((log.completed_sets / log.total_sets) * 100) }}%
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-gray-50 rounded-lg p-2">
                                    <div class="text-lg font-bold text-indigo-600">{{ log.duration_minutes || '--' }}</div>
                                    <div class="text-xs text-gray-500">min</div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-2">
                                    <div class="text-lg font-bold text-purple-600">{{ log.completed_sets }}</div>
                                    <div class="text-xs text-gray-500">series</div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal de Duplicación -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="showDuplicateModal"
                class="fixed inset-0 z-50 overflow-y-auto"
                @click.self="closeDuplicateModal"
            >
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>

                <!-- Modal Container -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div 
                            v-if="showDuplicateModal"
                            class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6"
                        >
                            <!-- Header -->
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-900">Clonar Rutina</h3>
                                    <p class="text-sm text-gray-500">Personaliza tu copia</p>
                                </div>
                                <button 
                                    @click="closeDuplicateModal"
                                    class="p-2 hover:bg-gray-100 rounded-lg transition"
                                >
                                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Content -->
                            <div class="mb-6">
                                <p class="text-gray-600 mb-4">
                                    Vas a crear una copia de <strong class="text-gray-900">{{ workout.name }}</strong> que podrás editar libremente.
                                </p>

                                <!-- Selector de Visibilidad -->
                                <div class="space-y-3">
                                    <p class="text-sm font-bold text-gray-700 mb-2">Visibilidad de la rutina:</p>
                                    
                                    <!-- Opción Privada -->
                                    <label 
                                        class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all"
                                        :class="!duplicateIsPublic 
                                            ? 'border-indigo-500 bg-indigo-50 shadow-md' 
                                            : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50'"
                                    >
                                        <input 
                                            type="radio" 
                                            :value="false"
                                            v-model="duplicateIsPublic"
                                            class="sr-only"
                                        />
                                        <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0"
                                            :class="!duplicateIsPublic ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-400'"
                                        >
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-bold text-gray-900 flex items-center gap-2">
                                                🔒 Privada
                                                <span v-if="!duplicateIsPublic" class="text-xs bg-indigo-600 text-white px-2 py-0.5 rounded-full">Seleccionada</span>
                                            </p>
                                            <p class="text-sm text-gray-500">Solo visible para ti</p>
                                        </div>
                                        <div v-if="!duplicateIsPublic" class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <div v-else class="w-6 h-6 rounded-full border-2 border-gray-300 flex-shrink-0"></div>
                                    </label>

                                    <!-- Opción Pública -->
                                    <label 
                                        class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all"
                                        :class="duplicateIsPublic 
                                            ? 'border-green-500 bg-green-50 shadow-md' 
                                            : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50'"
                                    >
                                        <input 
                                            type="radio" 
                                            :value="true"
                                            v-model="duplicateIsPublic"
                                            class="sr-only"
                                        />
                                        <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0"
                                            :class="duplicateIsPublic ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'"
                                        >
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-bold text-gray-900 flex items-center gap-2">
                                                🌐 Pública
                                                <span v-if="duplicateIsPublic" class="text-xs bg-green-600 text-white px-2 py-0.5 rounded-full">Seleccionada</span>
                                            </p>
                                            <p class="text-sm text-gray-500">Visible para todos los usuarios</p>
                                        </div>
                                        <div v-if="duplicateIsPublic" class="w-6 h-6 rounded-full bg-green-600 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <div v-else class="w-6 h-6 rounded-full border-2 border-gray-300 flex-shrink-0"></div>
                                    </label>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <button 
                                    @click="closeDuplicateModal"
                                    class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition"
                                >
                                    Cancelar
                                </button>
                                <button 
                                    @click="confirmDuplicate"
                                    class="flex-1 px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold hover:from-indigo-700 hover:to-purple-700 transition shadow-lg flex items-center justify-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Clonar
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>

        <!-- Modal de confirmación de eliminación -->
        <ConfirmModal
            :show="showDeleteModal"
            type="danger"
            title="¿Eliminar rutina?"
            message="Esta acción no se puede deshacer. Se eliminará permanentemente esta rutina y todos sus ejercicios."
            confirm-text="Sí, eliminar"
            cancel-text="Cancelar"
            @confirm="confirmDelete"
            @cancel="cancelDelete"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-fadeIn {
    animation: fadeIn 0.2s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
