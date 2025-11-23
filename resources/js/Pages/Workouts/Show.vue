<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useToast } from '@/composables/useToast';
import axios from 'axios';

const props = defineProps({
    workout: Object,
    isSaved: Boolean,
    isOwner: Boolean,
    title: String
});

const { success, error } = useToast();
const isSaving = ref(false);
const localIsSaved = ref(props.isSaved);
const localSaveCount = ref(props.workout.times_saved);
const expandedExercises = ref({});

const difficultyColors = {
    principiante: 'from-emerald-400 to-teal-500',
    intermedio: 'from-blue-500 to-indigo-600',
    avanzado: 'from-purple-500 to-pink-600'
};

const difficultyLabel = {
    principiante: 'Principiante',
    intermedio: 'Intermedio',
    avanzado: 'Avanzado'
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

const headerGradient = computed(() => {
    return difficultyColors[props.workout.difficulty_level] || difficultyColors.intermedio;
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

const deleteWorkout = () => {
    if (!confirm('¿Estás seguro de que quieres eliminar esta rutina? Esta acción no se puede deshacer.')) return;
    router.delete(route('workouts.destroy', props.workout.id));
};

const toggleExercise = (id) => {
    expandedExercises.value[id] = !expandedExercises.value[id];
};

const duplicateWorkout = () => {
    if (!confirm('¿Quieres crear una copia de esta rutina en tu perfil para editarla?')) return;
    router.post(route('workouts.duplicate', props.workout.id));
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
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10">
                            <p class="text-xs text-white/60 uppercase font-bold mb-1">Dificultad</p>
                            <p class="font-bold flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-white"></span>
                                {{ difficultyLabel[workout.difficulty_level] }}
                            </p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10">
                            <p class="text-xs text-white/60 uppercase font-bold mb-1">Duración</p>
                            <p class="font-bold">⏱️ {{ workout.duration_minutes || '--' }} min</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10">
                            <p class="text-xs text-white/60 uppercase font-bold mb-1">Categoría</p>
                            <p class="font-bold">{{ categoryIcons[workout.category] || '💪' }} {{ workout.category }}</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10">
                            <p class="text-xs text-white/60 uppercase font-bold mb-1">Guardados</p>
                            <p class="font-bold">🔖 {{ localSaveCount }} veces</p>
                        </div>
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
                            @click="duplicateWorkout"
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

        </div>
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
