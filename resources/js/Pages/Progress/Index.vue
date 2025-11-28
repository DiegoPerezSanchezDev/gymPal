<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
    title: String,
    workouts: Array,
    selectedWorkout: String,
    progressData: Object
});

const charts = ref([]);
const showDropdown = ref(false);

const hasData = computed(() => {
    return props.workouts && props.workouts.length > 0;
});

const changeWorkout = (workoutName) => {
    showDropdown.value = false;
    router.get(route('progress.index'), { workout: workoutName }, {
        preserveState: true,
        replace: true
    });
};

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

const selectedWorkoutData = computed(() => {
    return props.workouts?.find(w => w.name === props.selectedWorkout);
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
                        <h2 class="font-extrabold text-xl text-gray-900 leading-tight">
                            Mi Progreso
                        </h2>
                        <p class="text-xs text-gray-500 font-medium">Evolución por rutina</p>
                    </div>
                </div>
            </template>

            <div class="py-8">
                <div class="max-w-7xl mx-auto px-4 space-y-8">
                    
                    <!-- Estado vacío -->
                    <div v-if="!hasData" class="max-w-2xl mx-auto">
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-3xl p-12 text-center border-2 border-dashed border-indigo-200">
                            <div class="w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                                <span class="text-5xl">📊</span>
                            </div>
                            <h3 class="text-2xl font-black text-gray-900 mb-3">¡Empieza tu viaje fitness!</h3>
                            <p class="text-gray-600 mb-8 max-w-md mx-auto">
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
                                    class="inline-flex items-center gap-2 px-6 py-3 bg-white text-indigo-600 rounded-xl font-bold hover:bg-gray-50 transition shadow border-2 border-indigo-200"
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
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-md">
                                    <span class="text-2xl">🎯</span>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-gray-800">Selecciona una rutina</h3>
                                    <p class="text-sm text-gray-500">Analiza tu progreso en cada ejercicio</p>
                                </div>
                            </div>
                            
                            <!-- Dropdown Custom -->
                            <div class="relative">
                                <!-- Botón del selector -->
                                <button
                                    @click="toggleDropdown"
                                    class="w-full px-4 py-4 rounded-xl border-2 border-gray-200 bg-white hover:border-indigo-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all text-left flex items-center justify-between group"
                                >
                                    <div class="flex-1">
                                        <div class="font-bold text-gray-900 mb-0.5">{{ selectedWorkout }}</div>
                                        <div class="text-sm text-gray-500" v-if="selectedWorkoutData">
                                            {{ selectedWorkoutData.times_completed }} {{ selectedWorkoutData.times_completed === 1 ? 'sesión completada' : 'sesiones completadas' }}
                                        </div>
                                    </div>
                                    <svg 
                                        class="w-5 h-5 text-gray-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': showDropdown }"
                                        fill="none" 
                                        viewBox="0 0 24 24" 
                                        stroke="currentColor"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- Lista de opciones -->
                                <Transition
                                    enter-active-class="transition ease-out duration-200"
                                    enter-from-class="opacity-0 scale-95"
                                    enter-to-class="opacity-100 scale-100"
                                    leave-active-class="transition ease-in duration-150"
                                    leave-from-class="opacity-100 scale-100"
                                    leave-to-class="opacity-0 scale-95"
                                >
                                    <div
                                        v-if="showDropdown"
                                        class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-2xl border-2 border-gray-100 max-h-80 overflow-y-auto"
                                    >
                                        <button
                                            v-for="workout in workouts"
                                            :key="workout.name"
                                            @click="changeWorkout(workout.name)"
                                            class="w-full px-4 py-3 text-left hover:bg-indigo-50 transition-colors border-b border-gray-100 last:border-b-0 first:rounded-t-xl last:rounded-b-xl"
                                            :class="{ 'bg-indigo-50': selectedWorkout === workout.name }"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <div class="font-bold text-gray-900 mb-0.5">{{ workout.name }}</div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ workout.times_completed }} {{ workout.times_completed === 1 ? 'sesión' : 'sesiones' }}
                                                    </div>
                                                </div>
                                                <div v-if="selectedWorkout === workout.name" class="ml-3">
                                                    <div class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                </Transition>
                            </div>
                        </div>

                        <!-- Estadísticas de la Rutina -->
                        <div v-if="progressData && progressData.stats" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div :class="['rounded-2xl p-6 text-white shadow-lg text-center transform hover:scale-105 transition-transform duration-300 bg-gradient-to-br', statColors[0]]">
                                <div class="text-4xl font-black mb-1">{{ progressData.stats.times_completed }}</div>
                                <div class="text-xs opacity-80 font-bold uppercase tracking-wider">Veces Completada</div>
                            </div>
                            
                            <div :class="['rounded-2xl p-6 text-white shadow-lg text-center transform hover:scale-105 transition-transform duration-300 bg-gradient-to-br', statColors[1]]">
                                <div class="text-4xl font-black mb-1">{{ progressData.stats.avg_duration }}</div>
                                <div class="text-xs opacity-80 font-bold uppercase tracking-wider">Minutos Promedio</div>
                            </div>

                            <!-- Tarjeta Récords Personales (Dorada) -->
                            <button 
                                @click="openRecordsModal"
                                class="rounded-2xl p-6 text-white shadow-lg text-center transform hover:scale-105 transition-transform duration-300 bg-gradient-to-br from-yellow-400 to-orange-500 relative overflow-hidden group"
                            >
                                <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-10 transition-opacity"></div>
                                <div class="text-4xl font-black mb-1">🏆</div>
                                <div class="text-xs opacity-90 font-bold uppercase tracking-wider text-yellow-50">Récords Personales</div>
                                <div class="absolute top-2 right-2 text-yellow-200 opacity-50">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </div>
                            </button>

                            <div :class="['rounded-2xl p-6 text-white shadow-lg text-center transform hover:scale-105 transition-transform duration-300 bg-gradient-to-br', statColors[3]]">
                                <div class="text-2xl font-black mb-1">{{ progressData.stats.last_completed }}</div>
                                <div class="text-xs opacity-80 font-bold uppercase tracking-wider">Última Sesión</div>
                            </div>
                        </div>

                        <!-- Gráficos por Ejercicio -->
                        <div v-if="progressData && progressData.exercises" class="space-y-6">
                            <div
                                v-for="(exercise, index) in progressData.exercises"
                                :key="exercise.name"
                                class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100"
                            >
                                <div class="flex items-center justify-between mb-6">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                            <span class="w-2 h-6 bg-gradient-to-b from-indigo-500 to-purple-600 rounded-full"></span>
                                            {{ exercise.name }}
                                        </h3>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Récord: <span class="font-bold text-indigo-600">{{ exercise.max_weight }} kg</span>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-black text-gray-900">{{ exercise.sessions.length }}</div>
                                        <div class="text-xs text-gray-500 font-medium">sesiones</div>
                                    </div>
                                </div>
                                
                                <div class="h-64">
                                    <canvas :id="`chart-${index}`"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Mensaje si no hay datos de progreso -->
                        <div v-else class="bg-yellow-50 border-2 border-yellow-200 rounded-2xl p-8 text-center">
                            <div class="text-4xl mb-3">⚠️</div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">No hay datos de progreso para esta rutina</h3>
                            <p class="text-gray-600">Completa esta rutina al menos una vez para ver tu evolución.</p>
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

                        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
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
                                <p class="text-sm text-gray-500 mb-6">
                                    Tus mejores marcas (peso × repeticiones) en esta rutina.
                                </p>

                                <div v-if="progressData && progressData.personalRecords && progressData.personalRecords.length > 0" class="space-y-4">
                                    <div 
                                        v-for="(record, index) in progressData.personalRecords" 
                                        :key="record.name"
                                        class="flex items-center justify-between p-5 bg-gray-50 rounded-2xl border border-gray-100 hover:border-yellow-300 hover:bg-yellow-50 transition-all group"
                                    >
                                        <div class="flex items-center gap-4 flex-1 min-w-0">
                                            <div 
                                                class="w-10 h-10 flex-shrink-0 rounded-full flex items-center justify-center font-black text-sm shadow-sm"
                                                :class="index < 3 ? 'bg-yellow-100 text-yellow-700' : 'bg-white text-gray-500 border border-gray-200'"
                                            >
                                                {{ index + 1 }}
                                            </div>
                                            <div class="min-w-0 truncate pr-2">
                                                <div class="font-bold text-gray-900 text-base truncate">{{ record.name }}</div>
                                                <div class="text-xs text-gray-500 font-medium">{{ record.date }}</div>
                                            </div>
                                        </div>
                                        
                                        <div class="text-right pl-4 flex-shrink-0">
                                            <div class="flex items-baseline justify-end gap-1">
                                                <span class="font-black text-2xl text-gray-900 group-hover:text-orange-600 transition-colors tracking-tight">
                                                    {{ record.best_weight }}<span class="text-lg">kg</span>
                                                </span>
                                                <span class="text-gray-400 font-medium text-sm mx-1">×</span>
                                                <span class="font-black text-2xl text-gray-900 group-hover:text-orange-600 transition-colors">
                                                    {{ record.best_reps }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-12">
                                    <div class="text-4xl mb-3 opacity-50">📊</div>
                                    <p class="text-gray-400 font-medium">No hay récords registrados aún.</p>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 px-6 py-4">
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
