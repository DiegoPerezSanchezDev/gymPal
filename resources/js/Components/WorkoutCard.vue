<script setup>
import { computed } from 'vue';

const props = defineProps({
    workoutLog: Object
});

const formatTime = (minutes) => {
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    return h > 0 ? `${h}h ${m}m` : `${m}m`;
};

const getBestLift = () => {
    if (!props.workoutLog.exercises_data) return null;
    
    let maxWeight = 0;
    let bestExercise = '';
    
    props.workoutLog.exercises_data.forEach(ex => {
        ex.sets.forEach(set => {
            const weight = Number(set.weight);
            if (weight > maxWeight) {
                maxWeight = weight;
                bestExercise = ex.name;
            }
        });
    });
    
    return maxWeight > 0 ? { name: bestExercise, weight: maxWeight } : null;
};

const bestLift = computed(() => getBestLift());

const formattedDate = computed(() => {
    return new Date(props.workoutLog.created_at).toLocaleDateString('es-ES', {
        day: 'numeric',
        month: 'short'
    });
});
</script>

<template>
    <div class="w-full aspect-[16/9] bg-gradient-to-br from-gray-900 via-black to-gray-800 text-white p-6 flex flex-col justify-between relative rounded-2xl overflow-hidden">
        <!-- Glow Effects -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full blur-[100px] opacity-[0.08] -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-white rounded-full blur-[100px] opacity-[0.05] translate-y-1/2 -translate-x-1/2"></div>

        <!-- Header -->
        <div class="relative z-10 flex justify-between items-start">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-white/10 backdrop-blur-md rounded-xl flex items-center justify-center font-black text-sm border border-white/10">
                    ⚡
                </div>
                <div>
                    <div class="font-black tracking-widest text-xs">GYMPAL</div>
                    <div class="text-[9px] text-white/50 uppercase tracking-widest font-bold">Workout Log</div>
                </div>
            </div>
            <div class="px-2 py-1 rounded-full bg-white/5 border border-white/10 text-[9px] font-bold text-white/70">
                {{ formattedDate }}
            </div>
        </div>

        <!-- Main Content -->
        <div class="relative z-10 flex-1 flex flex-col justify-center space-y-4">
            <h1 class="text-2xl font-black leading-tight text-white line-clamp-2">
                {{ workoutLog.workout_name }}
            </h1>

            <div class="grid grid-cols-3 gap-2">
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-2 border border-white/5">
                    <div class="text-xl font-black text-white">{{ formatTime(workoutLog.duration_minutes) }}</div>
                    <div class="text-[8px] uppercase tracking-wider text-white/40 font-bold">Tiempo</div>
                </div>
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-2 border border-white/5">
                    <div class="text-xl font-black text-white">{{ workoutLog.completed_sets }}</div>
                    <div class="text-[8px] uppercase tracking-wider text-white/40 font-bold">Series</div>
                </div>
                <div v-if="bestLift" class="bg-gradient-to-r from-white/10 to-white/5 backdrop-blur-md rounded-xl p-2 border border-white/10">
                    <div class="text-xl font-black text-white">{{ bestLift.weight }}<span class="text-xs text-white/60">kg</span></div>
                    <div class="text-[8px] uppercase tracking-wider text-white/60 font-bold">Mejor</div>
                </div>
                <div v-else class="bg-white/5 backdrop-blur-sm rounded-xl p-2 border border-white/5">
                    <div class="text-xl font-black text-white">{{ workoutLog.exercises_data.length }}</div>
                    <div class="text-[8px] uppercase tracking-wider text-white/40 font-bold">Ejercicios</div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="relative z-10">
            <div class="h-px w-full bg-gradient-to-r from-transparent via-white/20 to-transparent mb-3"></div>
            <div class="flex items-center justify-between text-[9px] text-white/40 font-bold uppercase tracking-wider">
                <span>Entrenamiento Completado</span>
                <span>GymPal</span>
            </div>
        </div>
    </div>
</template>
