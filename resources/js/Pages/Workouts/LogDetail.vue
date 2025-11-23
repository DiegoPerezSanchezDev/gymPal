<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    log: Object,
    title: String
});

const completionPercentage = computed(() => {
    if (props.log.total_sets === 0) return 0;
    return Math.round((props.log.completed_sets / props.log.total_sets) * 100);
});

const formattedDate = computed(() => {
    return new Date(props.log.created_at).toLocaleString('es-ES', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
});
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="max-w-4xl mx-auto px-4">
                
                <!-- Header -->
                <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl shadow-2xl p-6 md:p-8 text-white mb-8">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <div class="flex-1">
                            <h1 class="text-2xl md:text-4xl font-black mb-2">🎉 ¡Entrenamiento Completado!</h1>
                            <p class="text-green-100 text-sm md:text-lg">{{ formattedDate }}</p>
                        </div>
                        <div class="text-left md:text-right">
                            <div class="text-4xl md:text-6xl font-black">{{ completionPercentage }}%</div>
                            <div class="text-sm text-green-100">Completado</div>
                        </div>
                    </div>
                    
                    <h2 class="text-xl md:text-3xl font-bold mb-4">{{ log.workout_name }}</h2>
                    
                    <div class="grid grid-cols-3 gap-2 md:gap-4">
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3 md:p-4">
                            <div class="text-xl md:text-3xl font-black">{{ log.duration_minutes || '--' }}</div>
                            <div class="text-xs md:text-sm text-green-100">Minutos</div>
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3 md:p-4">
                            <div class="text-xl md:text-3xl font-black">{{ log.completed_sets }}</div>
                            <div class="text-xs md:text-sm text-green-100">Series</div>
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3 md:p-4">
                            <div class="text-xl md:text-3xl font-black">{{ log.exercises_data.length }}</div>
                            <div class="text-xs md:text-sm text-green-100">Ejercicios</div>
                        </div>
                    </div>
                </div>

                <!-- Ejercicios -->
                <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">Detalle del Entrenamiento</h3>
                    
                    <div class="space-y-6">
                        <div 
                            v-for="(exercise, index) in log.exercises_data" 
                            :key="index"
                            class="border-2 border-gray-100 rounded-xl p-5"
                        >
                            <h4 class="text-xl font-bold text-gray-900 mb-4">{{ exercise.name }}</h4>
                            
                            <div class="space-y-2">
                                <div 
                                    v-for="(set, setIndex) in exercise.sets" 
                                    :key="setIndex"
                                    class="flex items-center gap-4 p-3 rounded-lg"
                                    :class="set.completed ? 'bg-green-50' : 'bg-gray-50'"
                                >
                                    <div class="flex-shrink-0">
                                        <div 
                                            class="w-6 h-6 rounded-full border-2 flex items-center justify-center"
                                            :class="set.completed ? 'bg-green-500 border-green-500' : 'border-gray-300'"
                                        >
                                            <svg v-if="set.completed" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-1 flex items-center gap-4">
                                        <span class="text-sm font-bold text-gray-500">Serie {{ setIndex + 1 }}</span>
                                        <span class="text-lg font-bold text-indigo-600">{{ set.reps }} reps</span>
                                        <span class="text-gray-400">×</span>
                                        <span class="text-lg font-bold text-purple-600">{{ set.weight }} kg</span>
                                        <span 
                                            v-if="set.type && set.type !== 'normal'"
                                            class="text-xs uppercase font-bold px-2 py-1 rounded bg-yellow-100 text-yellow-700"
                                        >
                                            {{ set.type }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="flex gap-4">
                    <Link 
                        :href="route('workout-logs.index')" 
                        class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 rounded-xl font-bold text-center transition"
                    >
                        Ver Historial
                    </Link>
                    <Link 
                        v-if="log.workout_id"
                        :href="route('workouts.show', log.workout_id)" 
                        class="flex-1 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-center transition"
                    >
                        Ver Rutina
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
