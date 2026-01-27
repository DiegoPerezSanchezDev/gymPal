<template>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 transition-colors">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Estadísticas
        </h3>

        <div class="grid grid-cols-2 gap-4">
            <!-- Entrenamientos Completados -->
            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-4 border border-indigo-100 dark:border-indigo-800 transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ stats.workouts_completed || 0 }}</span>
                    <span class="text-xl">💪</span>
                </div>
                <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Total Entrenos</p>
            </div>

            <!-- Días Activos Este Mes -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 border border-green-100 dark:border-green-800 transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.active_days_month || 0 }}</span>
                    <span class="text-xl">📅</span>
                </div>
                <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Días Activos (Mes)</p>
            </div>

            <!-- Racha de Actividad -->
            <div class="bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-xl p-4 border border-orange-100 dark:border-orange-800 transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ stats.streak_days || 0 }}</span>
                    <span class="text-xl">🔥</span>
                </div>
                <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Racha Actual</p>
            </div>

            <!-- Nivel de Progreso -->
            <div class="bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-xl p-4 border border-purple-100 dark:border-purple-800 transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ stats.level || 1 }}</span>
                    <span class="text-xl">⭐</span>
                </div>
                <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Nivel Atleta</p>
            </div>
        </div>

        <!-- Gráficas de Progreso Real -->
        <div v-if="showChart" class="mt-8 space-y-8">
            <!-- Gráfico de Frecuencia -->
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700">
                <h4 class="text-xs font-black text-gray-400 dark:text-gray-500 mb-6 uppercase tracking-[0.2em] flex items-center gap-2">
                    <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                    Consistencia (Días/Semana)
                </h4>
                <div class="h-44 relative group">
                    <canvas ref="frequencyChartRef"></canvas>
                </div>
            </div>

            <!-- Gráfico de Categorías -->
            <div v-if="progressCharts?.categories?.length" class="pt-6 border-t border-gray-100 dark:border-gray-700">
                <h4 class="text-xs font-black text-gray-400 dark:text-gray-500 mb-6 uppercase tracking-[0.2em] flex items-center gap-2">
                    <span class="w-2 h-2 bg-purple-500 rounded-full"></span>
                    Enfoque del Entrenamiento
                </h4>
                <div class="flex flex-col sm:flex-row items-center gap-8">
                    <div class="w-32 h-32 relative">
                        <canvas ref="categoryChartRef"></canvas>
                    </div>
                    <div class="flex-1 grid grid-cols-2 gap-x-6 gap-y-2">
                        <div v-for="cat in progressCharts.categories" :key="cat.category_name" class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: cat.color }"></div>
                            <span class="text-xs font-bold text-gray-600 dark:text-gray-300">{{ cat.icon }} {{ cat.category_name }}</span>
                            <span class="text-[10px] font-medium text-gray-400">{{ cat.count }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-4 italic text-center">
                Visualización basada en los últimos 3 meses de actividad.
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            workouts_completed: 0,
            active_days_month: 0,
            streak_days: 0,
            level: 1
        })
    },
    showChart: {
        type: Boolean,
        default: false
    },
    progressCharts: {
        type: Object,
        default: () => ({ weeklyFrequency: [], categories: [] })
    }
});

const frequencyChartRef = ref(null);
const categoryChartRef = ref(null);
let frequencyInstance = null;
let categoryInstance = null;



const initCharts = () => {
    // Verificar que los refs existen antes de inicializar
    if (!frequencyChartRef.value) {
        return;
    }

    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#9ca3af' : '#6b7280';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';

    // 1. Weekly Frequency Chart (Line)
    const hasFrequencyData = props.progressCharts?.weeklyFrequency?.length > 0;
    if (frequencyChartRef.value && hasFrequencyData) {
        if (frequencyInstance) frequencyInstance.destroy();
        
        const ctx = frequencyChartRef.value.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 150);
        gradient.addColorStop(0, 'rgba(99, 102, 241, 0.3)');
        gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');

        frequencyInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: props.progressCharts.weeklyFrequency.map(d => d.week),
                datasets: [{
                    label: 'Días',
                    data: props.progressCharts.weeklyFrequency.map(d => d.days),
                    borderColor: '#6366f1',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#6366f1',
                    pointHoverBorderWidth: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: { left: 5, right: 15, top: 10, bottom: 0 }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: isDark ? '#1f2937' : '#fff',
                        titleColor: isDark ? '#fff' : '#1f2937',
                        padding: 12,
                        callbacks: {
                            label: (context) => `Entrenos: ${context.parsed.y} días`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor, font: { size: 9, weight: '700' } }
                    },
                    y: {
                        beginAtZero: true,
                        max: 7,
                        grid: { color: gridColor },
                        ticks: { 
                            color: textColor, 
                            stepSize: 1,
                            font: { size: 10 }
                        }
                    }
                }
            }
        });
    }

    // 2. Category Distribution Chart (Donut)
    const hasCategoryData = props.progressCharts?.categories?.length > 0;
    if (categoryChartRef.value && hasCategoryData) {
        if (categoryInstance) categoryInstance.destroy();

        categoryInstance = new Chart(categoryChartRef.value, {
            type: 'doughnut',
            data: {
                labels: props.progressCharts.categories.map(c => c.category_name),
                datasets: [{
                    data: props.progressCharts.categories.map(c => c.count),
                    backgroundColor: props.progressCharts.categories.map(c => c.color),
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: true,
                        padding: 10,
                        callbacks: {
                            label: (context) => ` ${context.label}: ${context.parsed} sesiones`
                        }
                    }
                }
            }
        });
    }
};

onMounted(() => {
    if (props.showChart) {
        // Aumentamos el delay a 400ms para asegurar que la transición de Inertia/Vue ha terminado
        // y el canvas está visible y con dimensiones definitivas.
        setTimeout(initCharts, 400);
    }
});

// Limpieza al desmontar
import { onUnmounted } from 'vue';
onUnmounted(() => {
    if (frequencyInstance) frequencyInstance.destroy();
    if (categoryInstance) categoryInstance.destroy();
});

watch(() => props.progressCharts, () => {
    setTimeout(initCharts, 100);
}, { deep: true });
</script>
