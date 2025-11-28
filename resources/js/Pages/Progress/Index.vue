<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import SelectInput from '@/Components/SelectInput.vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
    title: String,
    workouts: Array,
    selectedWorkout: String,
    progressData: Object
});

const charts = ref([]);

const hasData = computed(() => {
    return props.workouts && props.workouts.length > 0;
});

const changeWorkout = (workoutName) => {
    router.get(route('progress.index'), { workout: workoutName }, {
        preserveState: true,
        replace: true
    });
};

const selectedWorkoutData = computed(() => {
    return props.workouts?.find(w => w.name === props.selectedWorkout);
});

// Opciones formateadas para SelectInput
const workoutOptions = computed(() => {
    if (!props.workouts) return [];
    return props.workouts.map(workout => ({
        value: workout.name,
        label: workout.name,
        description: `${workout.times_completed} ${workout.times_completed === 1 ? 'sesión completada' : 'sesiones completadas'}`
    }));
});

// Modal de récords personales
const showRecordsModal = ref(false);

const openRecordsModal = () => {
    showRecordsModal.value = true;
};

const closeRecordsModal = () => {
    showRecordsModal.value = false;
};

// Bloquear scroll del body cuando el modal está abierto
watch(showRecordsModal, (val) => {
    if (val) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

// Limpiar gráficos anteriores antes de crear nuevos
const destroyCharts = () => {
    charts.value.forEach(chart => {
        if (chart) chart.destroy();
    });
    charts.value = [];
};

watch(() => props.progressData, () => {
    destroyCharts();
    if (props.progressData) {
        setTimeout(createCharts, 100);
    }
}, { deep: true });

onMounted(() => {
    if (props.progressData) {
        createCharts();
    }
});

const createCharts = () => {
    if (!props.progressData || !props.progressData.exercises) return;

    props.progressData.exercises.forEach((exercise, index) => {
        const ctx = document.getElementById(`chart-${index}`);
        if (!ctx) return;

        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: exercise.sessions.map(s => s.date),
                datasets: [{
                    label: 'Peso Máximo (kg)',
                    data: exercise.sessions.map(s => s.max_weight),
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(99, 102, 241, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return 'Peso máximo: ' + context.parsed.y + ' kg';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                size: 11,
                                weight: 'bold'
                            },
                            callback: function(value) {
                                return value + ' kg';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 11,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        charts.value.push(chart);
    });
};

// Colores para las tarjetas de stats
const statColors = [
    'from-indigo-500 to-purple-600',
    'from-pink-500 to-rose-600',
    'from-emerald-500 to-teal-600',
    'from-orange-500 to-red-600'
];
</script>

<template>
    <div>
        <Head :title="title" />

        <AuthenticatedLayout>
            <template #header>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-400 to-red-500 rounded-xl flex items-center justify-center shadow-md text-white">
                        <span class="text-2xl">📈</span>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-xl text-gray-900 dark:text-white leading-tight transition-colors">
                            Mi Progreso
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium transition-colors">Evolución por rutina</p>
                    </div>
                </div>
            </template>

            <div class="py-8">
                <div class="max-w-7xl mx-auto px-4 space-y-8">
                    
                    <!-- Estado vacío -->
                    <div v-if="!hasData" class="max-w-2xl mx-auto">
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-3xl p-12 text-center border-2 border-dashed border-indigo-200 dark:border-indigo-800 transition-colors">
                            <div class="w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                                <span class="text-5xl">📊</span>
                            </div>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-3 transition-colors">¡Empieza tu viaje fitness!</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-8 max-w-md mx-auto transition-colors">
                                Completa tus primeras rutinas para ver gráficos de tu progreso y evolución en cada ejercicio.
                            </p>
                            <div class="flex gap-3 justify-center flex-wrap">
                                <Link 
                                    :href="route('workouts.index')"
                                    class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg hover:shadow-xl transform hover:scale-105"
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    Explorar Rutinas
                                </Link>
                                <Link 
                                    :href="route('workouts.my-workouts')"
                                    class="inline-flex items-center gap-2 px-6 py-3 bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 rounded-xl font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow border-2 border-indigo-200 dark:border-indigo-800 transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                    Mis Rutinas
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Selector de Rutina y Contenido -->
                    <template v-else>
                        <!-- Selector de Rutina (Dropdown Custom) -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 transition-colors">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-md">
                                    <span class="text-2xl">🎯</span>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-white transition-colors">Selecciona una rutina</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors">Analiza tu progreso en cada ejercicio</p>
                                </div>
                            </div>
                            
                            <!-- SelectInput Component -->
                            <SelectInput
                                id="workout-selector"
                                :model-value="selectedWorkout"
                                @update:model-value="changeWorkout"
                                :options="workoutOptions"
                            />
                        </div>

                        <!-- Estadísticas de la Rutina -->
                        <!-- Estadísticas de la Rutina (Nuevo Diseño) -->
                        <div v-if="progressData && progressData.stats" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                            <!-- Veces Completada -->
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col items-center justify-center text-center group hover:border-indigo-200 dark:hover:border-indigo-500 transition-colors">
                                <div class="w-12 h-12 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl mb-2 group-hover:scale-110 transition-transform">
                                    ✅
                                </div>
                                <div class="text-3xl font-black text-gray-900 dark:text-white leading-none transition-colors">{{ progressData.stats.times_completed }}</div>
                                <div class="text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mt-1 transition-colors">Completada</div>
                            </div>
                            
                            <!-- Minutos Promedio -->
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col items-center justify-center text-center group hover:border-blue-200 dark:hover:border-blue-500 transition-colors">
                                <div class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl mb-2 group-hover:scale-110 transition-transform">
                                    ⏱️
                                </div>
                                <div class="text-3xl font-black text-gray-900 dark:text-white leading-none transition-colors">{{ progressData.stats.avg_duration }}</div>
                                <div class="text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mt-1 transition-colors">Minutos Promedio</div>
                            </div>

                            <!-- Récords (Botón) -->
                            <button 
                                @click="openRecordsModal"
                                class="bg-gradient-to-br from-yellow-400 to-orange-500 p-4 rounded-2xl shadow-md flex flex-col items-center justify-center text-center text-white transform hover:scale-[1.02] transition-all"
                            >
                                <div class="text-3xl mb-1">🏆</div>
                                <div class="font-black text-lg leading-tight">Ver Récords</div>
                                <div class="text-[10px] opacity-80 font-bold uppercase tracking-wider mt-1">Personales</div>
                            </button>
                            
                            <!-- Última Sesión -->
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col items-center justify-center text-center group hover:border-emerald-200 dark:hover:border-emerald-500 transition-colors">
                                <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl mb-2 group-hover:scale-110 transition-transform">
                                    📅
                                </div>
                                <div class="text-lg font-black text-gray-900 dark:text-white leading-tight transition-colors">{{ progressData.stats.last_completed || 'N/A' }}</div>
                                <div class="text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mt-1 transition-colors">Última Sesión</div>
                            </div>
                        </div>

                        <!-- Gráficos por Ejercicio -->
                        <div v-if="progressData && progressData.exercises" class="space-y-6">
                            <div
                                v-for="(exercise, index) in progressData.exercises"
                                :key="exercise.name"
                                class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 transition-colors"
                            >
                                <div class="flex items-center justify-between mb-6">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2 transition-colors">
                                            <span class="w-2 h-6 bg-gradient-to-b from-indigo-500 to-purple-600 rounded-full"></span>
                                            {{ exercise.name }}
                                        </h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 transition-colors">
                                            Récord: <span class="font-bold text-indigo-600 dark:text-indigo-400 transition-colors">{{ exercise.max_weight }} kg</span>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-black text-gray-900 dark:text-white transition-colors">{{ exercise.sessions.length }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium transition-colors">sesiones</div>
                                    </div>
                                </div>
                                
                                <div class="h-64">
                                    <canvas :id="`chart-${index}`"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Mensaje si no hay datos de progreso -->
                        <div v-else class="bg-yellow-50 dark:bg-yellow-900/20 border-2 border-yellow-200 dark:border-yellow-800 rounded-2xl p-8 text-center transition-colors">
                            <div class="text-4xl mb-3">⚠️</div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 transition-colors">No hay datos de progreso para esta rutina</h3>
                            <p class="text-gray-600 dark:text-gray-300 transition-colors">Completa esta rutina al menos una vez para ver tu evolución.</p>
                        </div>
                    </template>

                </div>
            </div>

            <!-- Modal de Récords Personales -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showRecordsModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <!-- Background overlay -->
                        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeRecordsModal"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                            <div class="bg-gradient-to-r from-yellow-400 to-orange-500 px-6 py-4 flex justify-between items-center">
                                <h3 class="text-lg leading-6 font-black text-white flex items-center gap-2" id="modal-title">
                                    <span>🏆</span> Récords Personales
                                </h3>
                                <button @click="closeRecordsModal" class="text-yellow-100 hover:text-white transition">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="px-6 py-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 transition-colors">
                                    Tus mejores marcas (peso × repeticiones) en esta rutina.
                                </p>

                                <div v-if="progressData && progressData.personalRecords && progressData.personalRecords.length > 0" class="space-y-4">
                                    <div 
                                        v-for="(record, index) in progressData.personalRecords" 
                                        :key="record.name"
                                        class="flex items-center justify-between p-5 bg-gray-50 dark:bg-gray-700 rounded-2xl border border-gray-100 dark:border-gray-600 hover:border-yellow-300 dark:hover:border-yellow-500 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 transition-all group"
                                    >
                                        <div class="flex items-center gap-4 flex-1 min-w-0">
                                            <div 
                                                class="w-10 h-10 flex-shrink-0 rounded-full flex items-center justify-center font-black text-sm shadow-sm transition-colors"
                                                :class="index < 3 ? 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-400' : 'bg-white dark:bg-gray-600 text-gray-500 dark:text-gray-300 border border-gray-200 dark:border-gray-500'"
                                            >
                                                {{ index + 1 }}
                                            </div>
                                            <div class="min-w-0 truncate pr-2">
                                                <div class="font-bold text-gray-900 dark:text-white text-base truncate transition-colors">{{ record.name }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium transition-colors">{{ record.date }}</div>
                                            </div>
                                        </div>
                                        
                                        <div class="text-right pl-4 flex-shrink-0">
                                            <div class="flex items-baseline justify-end gap-1">
                                                <span class="font-black text-2xl text-gray-900 dark:text-white group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors tracking-tight">
                                                    {{ record.best_weight }}<span class="text-lg">kg</span>
                                                </span>
                                                <span class="text-gray-400 dark:text-gray-500 font-medium text-sm mx-1 transition-colors">×</span>
                                                <span class="font-black text-2xl text-gray-900 dark:text-white group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                                                    {{ record.best_reps }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-12">
                                    <div class="text-4xl mb-3 opacity-50">📊</div>
                                    <p class="text-gray-400 dark:text-gray-500 font-medium transition-colors">No hay récords registrados aún.</p>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 transition-colors">
                                <button 
                                    type="button" 
                                    class="w-full inline-flex justify-center items-center rounded-xl border border-transparent shadow-lg px-4 py-3.5 bg-gray-900 text-base font-bold text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition-transform active:scale-95"
                                    @click="closeRecordsModal"
                                >
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </AuthenticatedLayout>
    </div>
</template>
