<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    logs: Array,
    currentMonth: String,
    currentYear: Number,
    title: String
});

const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
const dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

// Crear mapa de logs por día
const logsByDate = computed(() => {
    const map = {};
    props.logs.forEach(log => {
        const date = new Date(log.created_at).toISOString().split('T')[0];
        if (!map[date]) map[date] = [];
        map[date].push(log);
    });
    return map;
});

// Generar días del mes
const calendarDays = computed(() => {
    const year = props.currentYear;
    const month = monthNames.indexOf(props.currentMonth);
    
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();
    
    const days = [];
    
    // Días vacíos al inicio
    for (let i = 0; i < startingDayOfWeek; i++) {
        days.push({ day: null, date: null });
    }
    
    // Días del mes
    for (let day = 1; day <= daysInMonth; day++) {
        const date = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        days.push({
            day,
            date,
            logs: logsByDate.value[date] || [],
            isToday: date === new Date().toISOString().split('T')[0]
        });
    }
    
    return days;
});

// Estadísticas del mes
const monthStats = computed(() => {
    return {
        totalWorkouts: props.logs.length,
        totalSets: props.logs.reduce((sum, log) => sum + log.completed_sets, 0),
        avgCompletion: props.logs.length > 0 
            ? Math.round(props.logs.reduce((sum, log) => sum + (log.completed_sets / log.total_sets * 100), 0) / props.logs.length)
            : 0
    };
});

function previousMonth() {
    const month = monthNames.indexOf(props.currentMonth);
    const newMonth = month === 0 ? 11 : month - 1;
    const newYear = month === 0 ? props.currentYear - 1 : props.currentYear;
    window.location.href = route('workout-logs.calendar', { month: newMonth + 1, year: newYear });
}

function nextMonth() {
    const month = monthNames.indexOf(props.currentMonth);
    const newMonth = month === 11 ? 0 : month + 1;
    const newYear = month === 11 ? props.currentYear + 1 : props.currentYear;
    window.location.href = route('workout-logs.calendar', { month: newMonth + 1, year: newYear });
}
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-md">
                        <span class="text-2xl">📅</span>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-xl text-gray-900 dark:text-white leading-tight transition-colors">
                            Calendario de Entrenamientos
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium transition-colors">{{ currentMonth }} {{ currentYear }}</p>
                    </div>
                </div>
                <Link 
                    :href="route('workout-logs.index')"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 transition text-sm"
                >
                    Ver Lista
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-6xl mx-auto px-4">
                
                <!-- Stats del mes -->
                <div class="grid grid-cols-3 gap-4 mb-8">
                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg text-center">
                        <div class="text-4xl font-black mb-1">{{ monthStats.totalWorkouts }}</div>
                        <div class="text-sm text-indigo-100">Entrenamientos</div>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-6 text-white shadow-lg text-center">
                        <div class="text-4xl font-black mb-1">{{ monthStats.totalSets }}</div>
                        <div class="text-sm text-green-100">Series totales</div>
                    </div>
                    <div class="bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl p-6 text-white shadow-lg text-center">
                        <div class="text-4xl font-black mb-1">{{ monthStats.avgCompletion }}%</div>
                        <div class="text-sm text-orange-100">Completitud media</div>
                    </div>
                </div>

                <!-- Navegación del mes -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-6 transition-colors">
                    <div class="flex items-center justify-between mb-6">
                        <button @click="previousMonth" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition text-gray-700 dark:text-gray-200">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white transition-colors">{{ currentMonth }} {{ currentYear }}</h3>
                        <button @click="nextMonth" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition text-gray-700 dark:text-gray-200">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <!-- Días de la semana -->
                    <div class="grid grid-cols-7 gap-2 mb-2">
                        <div v-for="day in dayNames" :key="day" class="text-center text-sm font-bold text-gray-500 dark:text-gray-400 py-2 transition-colors">
                            {{ day }}
                        </div>
                    </div>

                    <!-- Días del mes -->
                    <div class="grid grid-cols-7 gap-2">
                        <div 
                            v-for="(dayData, index) in calendarDays" 
                            :key="index"
                            class="aspect-square"
                        >
                            <div 
                                v-if="dayData.day"
                                class="h-full rounded-lg border-2 transition-all p-2 flex flex-col"
                                :class="[
                                    dayData.isToday ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 dark:border-indigo-400' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600',
                                    dayData.logs.length > 0 ? 'bg-green-50 dark:bg-green-900/30 border-green-300 dark:border-green-700' : 'bg-white dark:bg-gray-800'
                                ]"
                            >
                                <div class="text-sm font-bold mb-1 transition-colors" :class="dayData.isToday ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-200'">
                                    {{ dayData.day }}
                                </div>
                                <div v-if="dayData.logs.length > 0" class="flex-1 flex flex-col gap-1">
                                    <Link 
                                        v-for="log in dayData.logs.slice(0, 2)" 
                                        :key="log.id"
                                        :href="route('workout-logs.show', log.id)"
                                        class="text-xs bg-green-500 dark:bg-green-600 text-white rounded px-1 py-0.5 truncate hover:bg-green-600 dark:hover:bg-green-500 transition"
                                        :title="log.workout_name"
                                    >
                                        💪 {{ log.completed_sets }}
                                    </Link>
                                    <div v-if="dayData.logs.length > 2" class="text-xs text-green-600 dark:text-green-400 font-bold transition-colors">
                                        +{{ dayData.logs.length - 2 }}
                                    </div>
                                </div>
                            </div>
                            <div v-else class="h-full"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
