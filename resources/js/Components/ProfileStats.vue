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
                    <span class="text-2xl">💪</span>
                    <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ stats.workouts_completed || 0 }}</span>
                </div>
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">Entrenamientos</p>
            </div>

            <!-- Días Activos Este Mes -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 border border-green-100 dark:border-green-800 transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-2xl">📅</span>
                    <span class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.active_days_month || 0 }}</span>
                </div>
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">Días Activos</p>
            </div>

            <!-- Racha de Actividad -->
            <div class="bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-xl p-4 border border-orange-100 dark:border-orange-800 transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-2xl">🔥</span>
                    <span class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ stats.streak_days || 0 }}</span>
                </div>
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">Racha (días)</p>
            </div>

            <!-- Total Rutinas -->
            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-xl p-4 border border-blue-100 dark:border-blue-800 transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-2xl">🏋️</span>
                    <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.workouts_created || 0 }}</span>
                </div>
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">Rutinas Creadas</p>
            </div>

            <!-- Total Posts -->
            <div class="bg-gradient-to-br from-pink-50 to-rose-50 dark:from-pink-900/20 dark:to-rose-900/20 rounded-xl p-4 border border-pink-100 dark:border-pink-800 transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-2xl">📝</span>
                    <span class="text-2xl font-bold text-pink-600 dark:text-pink-400">{{ stats.posts_count || 0 }}</span>
                </div>
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">Publicaciones</p>
            </div>

            <!-- Nivel de Progreso -->
            <div class="bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-xl p-4 border border-purple-100 dark:border-purple-800 transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-2xl">⭐</span>
                    <span class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ stats.level || 1 }}</span>
                </div>
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">Nivel</p>
            </div>
        </div>

        <!-- Gráfica de Progreso (placeholder por ahora) -->
        <div v-if="showChart" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-4">Actividad del Mes</h4>
            <div class="h-32 bg-gray-50 dark:bg-gray-700/50 rounded-lg flex items-center justify-center transition-colors">
                <p class="text-sm text-gray-400 dark:text-gray-500">Gráfica próximamente</p>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    stats: {
        type: Object,
        default: () => ({
            workouts_completed: 0,
            active_days_month: 0,
            streak_days: 0,
            workouts_created: 0,
            posts_count: 0,
            level: 1
        })
    },
    showChart: {
        type: Boolean,
        default: false
    }
});
</script>
