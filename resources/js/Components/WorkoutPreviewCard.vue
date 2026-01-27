<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    workout: {
        type: Object,
        required: true
    }
});



const difficultyColors = {
    Principiante: 'from-emerald-500 to-teal-600',
    Intermedio: 'from-blue-500 to-indigo-600',
    Avanzado: 'from-purple-500 to-pink-600'
};

const difficultyLabel = {
    Principiante: 'Principiante',
    Intermedio: 'Intermedio',
    Avanzado: 'Avanzado'
};

const categoryIcon = computed(() => {
    return props.workout.category?.icon || '💪';
});

const gradientClass = computed(() => {
    return difficultyColors[props.workout.difficulty] || difficultyColors.Intermedio;
});

const workoutUrl = computed(() => {
    const isFromMyWorkouts = window.location.pathname.includes('my-workouts');
    const baseUrl = route('workouts.show', props.workout.id);
    return isFromMyWorkouts ? `${baseUrl}?from=my-workouts` : baseUrl;
});
const cleanCategoryName = computed(() => {
    return props.workout.category?.name || props.workout.category || 'General';
});
</script>

<template>
    <Link 
        :href="workoutUrl"
        class="block w-full bg-gradient-to-br text-white p-5 rounded-2xl overflow-hidden relative group active:scale-[0.98] transition-all duration-200 shadow-md"
        :class="['bg-gradient-to-br', gradientClass]"
    >
        <!-- Glow Effects -->
        <div class="absolute top-0 right-0 w-48 h-48 bg-white rounded-full blur-[80px] opacity-10 -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-black rounded-full blur-[80px] opacity-10 translate-y-1/2 -translate-x-1/2"></div>

        <!-- Header -->
        <div class="relative z-10 flex justify-between items-start mb-4">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center text-2xl border border-white/20">
                    {{ categoryIcon }}
                </div>
                <div>
                    <div class="font-black tracking-widest text-xs">GYMPAL</div>
                    <div class="text-[9px] text-white/70 uppercase tracking-widest font-bold">Rutina</div>
                </div>
            </div>
            <div class="flex gap-2">
                <!-- Badge de visibilidad -->
                <div v-if="workout.is_public" class="px-2 py-1 rounded-full text-[9px] font-bold text-white bg-emerald-500/30 backdrop-blur-sm border border-white/30 flex items-center gap-1">
                    <span>🌐</span>
                    <span>Pública</span>
                </div>
                <div v-else class="px-2 py-1 rounded-full text-[9px] font-bold text-white bg-rose-500/30 backdrop-blur-sm border border-white/20 flex items-center gap-1">
                    <span>🔒</span>
                    <span>Privada</span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="relative z-10 mb-4">
            <h3 class="text-xl font-black leading-tight text-white mb-2 line-clamp-2">
                {{ workout.name }}
            </h3>
            <p v-if="workout.description" class="text-sm text-white/60 line-clamp-2">
                {{ workout.description }}
            </p>
        </div>

        <!-- Stats -->
        <div class="relative z-10 grid grid-cols-3 gap-2">
            <!-- Ejercicios -->
            <div class="bg-black/20 backdrop-blur-sm rounded-xl p-2 border border-white/10 flex flex-col items-center justify-center text-center">
                <span class="text-lg mb-1">🏋️</span>
                <div class="text-base font-black text-white leading-none">{{ workout.exercises?.length || 0 }}</div>
                <div class="text-[8px] uppercase tracking-wider text-white/70 font-bold mt-1">Ejercicios</div>
            </div>
            
            <!-- Duración -->
            <div class="bg-black/20 backdrop-blur-sm rounded-xl p-2 border border-white/10 flex flex-col items-center justify-center text-center">
                <span class="text-lg mb-1">⏱️</span>
                <div class="text-base font-black text-white leading-none">{{ workout.duration_minutes || '--' }}</div>
                <div class="text-[8px] uppercase tracking-wider text-white/70 font-bold mt-1">Minutos</div>
            </div>

            <!-- Dificultad -->
            <div class="bg-black/20 backdrop-blur-sm rounded-xl p-2 border border-white/10 flex flex-col items-center justify-center text-center">
                <span class="text-lg mb-1">⚡</span>
                <div class="text-base font-black text-white leading-none truncate w-full px-1">{{ workout.difficulty || 'N/A' }}</div>
                <div class="text-[8px] uppercase tracking-wider text-white/70 font-bold mt-1">Nivel</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="relative z-10 mt-4">
            <div class="h-px w-full bg-gradient-to-r from-transparent via-white/20 to-transparent mb-2"></div>
            <div class="flex items-center justify-between text-[9px] text-white/40 font-bold uppercase tracking-wider">
                <span>Toca para ver detalles</span>
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </div>
    </Link>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
