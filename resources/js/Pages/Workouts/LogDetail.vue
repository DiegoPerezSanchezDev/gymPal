<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ShareModal from '@/Components/ShareModal.vue';

const props = defineProps({
    log: Object,
    title: String
});

const showShareModal = ref(false);

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

const setTypeLabels = {
    normal: 'Normal',
    warmup: 'Calentamiento',
    drop_set: 'Drop Set',
    failure: 'Fallo'
};
</script>

<template>
    <Head :title="title" />

    <ShareModal 
        :show="showShareModal" 
        :log="log" 
        :user="$page.props.auth.user"
        @close="showShareModal = false" 
    />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="max-w-4xl mx-auto px-4">
                
                <!-- Header -->
                <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl shadow-2xl p-6 md:p-8 text-white mb-8 relative overflow-hidden">
                    <!-- Botón Compartir -->
                    <button 
                        @click="showShareModal = true"
                        class="absolute top-4 right-4 bg-white/20 hover:bg-white/30 backdrop-blur-md text-white px-4 py-2 rounded-xl font-bold text-sm transition flex items-center gap-2 z-10"
                    >
                        📸 <span class="hidden sm:inline">Compartir</span>
                    </button>

                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <div class="flex-1">
                            <h1 class="text-2xl md:text-4xl font-black mb-2">🎉 ¡Entrenamiento Completado!</h1>
                            <p class="text-green-100 text-sm md:text-lg">{{ formattedDate }}</p>
                        </div>
                        <div class="text-left md:text-right mt-8 md:mt-0">
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

                <!-- Notas del entrenamiento -->
                <div v-if="log.notes" class="bg-yellow-50 dark:bg-yellow-900/20 border-2 border-yellow-200 dark:border-yellow-800 rounded-2xl p-6 mb-6 transition-colors">
                    <h3 class="text-lg font-bold text-yellow-800 dark:text-yellow-400 mb-2 flex items-center gap-2 transition-colors">
                        <span>📝</span> Notas Personales
                    </h3>
                    <p class="text-yellow-900 dark:text-yellow-200 italic whitespace-pre-line transition-colors">{{ log.notes }}</p>
                </div>

                <!-- Ejercicios -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-6 transition-colors">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 transition-colors">Detalle del Entrenamiento</h3>
                    
                    <div class="space-y-6">
                        <div 
                            v-for="(exercise, index) in log.exercises_data" 
                            :key="index"
                            class="border-2 border-gray-100 dark:border-gray-700 rounded-xl p-5 transition-colors"
                        >
                            <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-4 transition-colors">{{ exercise.name }}</h4>
                            
                            <div class="space-y-3">
                                <div 
                                    v-for="(set, setIndex) in exercise.sets" 
                                    :key="setIndex"
                                    class="flex items-center gap-4 p-3 rounded-xl border border-gray-100 dark:border-gray-700 transition-colors"
                                    :class="set.completed ? 'bg-green-50/50 dark:bg-green-900/20' : 'bg-gray-50 dark:bg-gray-700'"
                                >
                                    <!-- Check Icon -->
                                    <div class="flex-shrink-0">
                                        <div 
                                            class="w-8 h-8 rounded-full border-2 flex items-center justify-center transition-colors"
                                            :class="set.completed ? 'bg-green-500 border-green-500' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700'"
                                        >
                                            <svg v-if="set.completed" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Set Info (Vertical Design) -->
                                    <div class="flex-1 flex flex-col items-center justify-center gap-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider transition-colors">Serie {{ setIndex + 1 }}</span>
                                            <span 
                                                v-if="set.type && set.type !== 'normal'"
                                                class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 transition-colors"
                                            >
                                                {{ setTypeLabels[set.type] || set.type }}
                                            </span>
                                        </div>
                                        
                                        <div class="flex items-center gap-3">
                                            <!-- Reps -->
                                            <div class="flex flex-col items-center">
                                                <div class="text-xl font-black text-indigo-600 dark:text-indigo-400 leading-none transition-colors">
                                                    {{ set.reps || 0 }}
                                                </div>
                                                <span class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase mt-0.5 transition-colors">Reps</span>
                                            </div>

                                            <span class="text-gray-300 dark:text-gray-600 text-xl font-light transition-colors">/</span>

                                            <!-- Peso -->
                                            <div class="flex flex-col items-center">
                                                <div class="text-xl font-black text-purple-600 dark:text-purple-400 leading-none transition-colors">
                                                    {{ set.weight || 0 }}
                                                </div>
                                                <span class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase mt-0.5 transition-colors">Kg</span>
                                            </div>
                                        </div>
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
                        class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl font-bold text-center transition-colors"
                    >
                        Ver Historial
                    </Link>
                    <Link 
                        v-if="log.workout_id"
                        :href="route('workouts.show', log.workout_id)" 
                        class="flex-1 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-xl font-bold text-center transition-colors"
                    >
                        Ver Rutina
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
