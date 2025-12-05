<!-- Componente para mostrar una rutina compartida en el chat -->
<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    workoutData: {
        type: Object,
        required: true,
    },
});

// Mapeo de colores según dificultad
const difficultyColors = computed(() => {
    const difficulty = props.workoutData.workout_difficulty;
    
    if (difficulty === 'Principiante') {
        return {
            bg: 'bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20',
            border: 'border-green-200 dark:border-green-800',
            icon: 'bg-green-500',
            text: 'text-green-900 dark:text-green-200',
            subtext: 'text-green-600 dark:text-green-400',
        };
    } else if (difficulty === 'Intermedio') {
        return {
            bg: 'bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20',
            border: 'border-blue-200 dark:border-blue-800',
            icon: 'bg-blue-500',
            text: 'text-blue-900 dark:text-blue-200',
            subtext: 'text-blue-600 dark:text-blue-400',
        };
    } else if (difficulty === 'Avanzado') {
        return {
            bg: 'bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20',
            border: 'border-purple-200 dark:border-purple-800',
            icon: 'bg-purple-500',
            text: 'text-purple-900 dark:text-purple-200',
            subtext: 'text-purple-600 dark:text-purple-400',
        };
    }
    
    // Default
    return {
        bg: 'bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20',
        border: 'border-indigo-200 dark:border-indigo-800',
        icon: 'bg-indigo-500',
        text: 'text-indigo-900 dark:text-indigo-200',
        subtext: 'text-indigo-600 dark:text-indigo-400',
    };
});
</script>

<template>
    <Link 
        :href="workoutData.workout_url || route('workouts.show', workoutData.workout_id)" 
        class="block bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all overflow-hidden"
    >
        <!-- Header de rutina compartida -->
        <div class="p-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex items-center gap-2 transition-colors">
            <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400 flex-shrink-0 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                <path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z" />
            </svg>
            <span class="text-xs text-gray-600 dark:text-gray-400 font-medium transition-colors">Rutina compartida</span>
        </div>

        <!-- Contenido de la rutina -->
        <div class="p-3">
            <!-- Autor de la rutina -->
            <div class="flex items-center gap-2 mb-3">
                <img
                    :src="workoutData.workout_author_profile_picture ? `/storage/${workoutData.workout_author_profile_picture}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(workoutData.workout_author_name || 'Usuario')}&background=random`"
                    :alt="workoutData.workout_author_name"
                    class="w-8 h-8 rounded-full object-cover"
                />
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm text-gray-800 dark:text-gray-200 truncate">
                        {{ workoutData.workout_author_name }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Creador de la rutina</p>
                </div>
            </div>

            <!-- Tarjeta de la rutina con colores dinámicos -->
            <div 
                class="rounded-lg p-3 border"
                :class="[difficultyColors.bg, difficultyColors.border]"
            >
                <div class="flex items-start gap-2">
                    <div 
                        class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center"
                        :class="difficultyColors.icon"
                    >
                        <span class="text-white text-lg">🏋️</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p 
                            class="font-bold text-base truncate mb-1"
                            :class="difficultyColors.text"
                        >
                            {{ workoutData.workout_name }}
                        </p>
                        <p 
                            v-if="workoutData.workout_description" 
                            class="text-xs mb-2 line-clamp-2"
                            :class="difficultyColors.subtext"
                        >
                            {{ workoutData.workout_description }}
                        </p>
                        <div 
                            class="flex items-center gap-3 text-xs flex-wrap"
                            :class="difficultyColors.subtext"
                        >
                            <span v-if="workoutData.workout_difficulty" class="flex items-center gap-1 font-medium">
                                📊 {{ workoutData.workout_difficulty }}
                            </span>
                            <span v-if="workoutData.workout_category" class="flex items-center gap-1">
                                🎯 {{ workoutData.workout_category }}
                            </span>
                            <span v-if="workoutData.workout_duration_minutes" class="flex items-center gap-1">
                                ⏱️ {{ workoutData.workout_duration_minutes }}'
                            </span>
                            <span v-if="workoutData.workout_exercises_count" class="flex items-center gap-1">
                                💪 {{ workoutData.workout_exercises_count }} ejercicios
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer con CTA -->
            <div class="mt-3 pt-2 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <span class="text-xs text-gray-500 dark:text-gray-400">Toca para ver detalles</span>
                <div class="flex items-center gap-1 text-indigo-600 dark:text-indigo-400 font-medium text-sm">
                    <span>Ver rutina</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
        </div>
    </Link>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
