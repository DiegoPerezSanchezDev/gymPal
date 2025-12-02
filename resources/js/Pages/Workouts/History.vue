<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import CalendarFilter from '@/Components/CalendarFilter.vue';
import axios from 'axios';

const props = defineProps({
    logs: Object,
    title: String,
    filters: Object,
    activityDates: Array,
    stats: Object
});

const search = ref(props.filters?.search || '');

// Usar los filtros que vienen del backend
const dateRange = ref({
    start: props.filters?.date_from || null,
    end: props.filters?.date_to || null
});

const showCalendar = ref(false);

// Scroll Infinito
const allLogs = ref([...props.logs.data]);
const isLoadingMore = ref(false);
const nextPageUrl = ref(props.logs.next_page_url);

// Actualizar lista cuando cambian los props (filtros)
watch(() => props.logs, (newLogs) => {
    allLogs.value = [...newLogs.data];
    nextPageUrl.value = newLogs.next_page_url;
}, { deep: true });

const loadMoreItems = async () => {
    if (isLoadingMore.value || !nextPageUrl.value) return;

    isLoadingMore.value = true;

    try {
        const res = await axios.get(nextPageUrl.value, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            params: {
                search: search.value,
                date_from: dateRange.value.start,
                date_to: dateRange.value.end
            }
        });
        
        const data = res.data;
        const newLogs = data.logs || data;
        
        if (newLogs && newLogs.data) {
            allLogs.value = [...allLogs.value, ...newLogs.data];
            nextPageUrl.value = newLogs.next_page_url;
        }
    } catch (err) {
        console.error("Error cargando más logs", err);
    } finally {
        isLoadingMore.value = false;
    }
};

const handleScroll = () => {
    const scrollPosition = window.innerHeight + window.scrollY;
    const documentHeight = document.documentElement.scrollHeight;
    
    if (scrollPosition >= documentHeight - 300) {
        loadMoreItems();
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

const updateFilters = debounce(() => {
    router.get(route('workout-logs.index'), { 
        search: search.value,
        date_from: dateRange.value.start,
        date_to: dateRange.value.end
    }, { 
        preserveState: true, 
        replace: true 
    });
}, 300);

// Observar cambios en los filtros
watch([search, dateRange], updateFilters, { deep: true });

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('es-ES', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
}

function formatTime(dateString) {
    return new Date(dateString).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>

<template>
    <div>
        <Head :title="title" />

        <AuthenticatedLayout>
            <template #header>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-md">
                            <span class="text-2xl">📊</span>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-xl text-gray-900 dark:text-white leading-tight transition-colors">
                                Historial de Entrenamientos
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium transition-colors">Tu progreso y dedicación</p>
                        </div>
                    </div>
                    <Link 
                        :href="route('workout-logs.calendar')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition text-sm flex items-center gap-2"
                    >
                        📅 Calendario Completo
                    </Link>
                </div>
            </template>

            <div class="py-8">
                <div class="max-w-4xl mx-auto px-4">
                    
                    <!-- Estadísticas generales -->
                    <div v-if="stats" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                        <!-- Total -->
                        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg text-center transform hover:scale-105 transition-transform duration-300">
                            <div class="text-4xl font-black mb-1">{{ stats.total_workouts }}</div>
                            <div class="text-xs text-indigo-100 font-bold uppercase tracking-wider opacity-80">Total Entrenamientos</div>
                        </div>
                        
                        <!-- Este Mes -->
                        <div class="bg-gradient-to-br from-pink-500 to-rose-600 rounded-2xl p-6 text-white shadow-lg text-center transform hover:scale-105 transition-transform duration-300">
                            <div class="text-4xl font-black mb-1">{{ stats.this_month }}</div>
                            <div class="text-xs text-pink-100 font-bold uppercase tracking-wider opacity-80">Este Mes</div>
                        </div>

                        <!-- Tiempo Total -->
                        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-lg text-center transform hover:scale-105 transition-transform duration-300">
                            <div class="text-4xl font-black mb-1">{{ Math.round(stats.total_minutes / 60) }}h</div>
                            <div class="text-xs text-emerald-100 font-bold uppercase tracking-wider opacity-80">Tiempo Total</div>
                        </div>
                    </div>

                    <!-- Filtros (Acordeón) -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 mb-6 transition-colors">
                        <div class="max-w-2xl mx-auto">
                            <button 
                                @click="showCalendar = !showCalendar"
                                class="w-full flex items-center justify-between p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition group"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/50 transition">
                                        📅
                                    </div>
                                    <div class="text-left">
                                        <h3 class="font-bold text-gray-800 dark:text-gray-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">Filtrar por Fecha</h3>
                                        <p v-if="dateRange.start" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 transition-colors">
                                            {{ formatDate(dateRange.start) }} 
                                            <span v-if="dateRange.end">- {{ formatDate(dateRange.end) }}</span>
                                        </p>
                                        <p v-else class="text-xs text-gray-400 dark:text-gray-500 transition-colors">Desplegar calendario</p>
                                    </div>
                                </div>
                                <svg 
                                    class="w-5 h-5 text-gray-400 dark:text-gray-500 transition-transform duration-300"
                                    :class="{'rotate-180': showCalendar}"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div v-show="showCalendar" class="mt-4 border-t border-gray-100 dark:border-gray-700 pt-4 transition-colors">
                                <CalendarFilter 
                                    v-model="dateRange" 
                                    :activity-dates="activityDates"
                                    @range-completed="showCalendar = false"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Lista de entrenamientos -->
                    <div v-if="allLogs.length > 0" class="space-y-4">
                        <Link 
                            v-for="log in allLogs" 
                            :key="log.id"
                            :href="route('workout-logs.show', log.id)"
                            class="block bg-white dark:bg-gray-800 rounded-2xl shadow-md hover:shadow-xl transition-all border-2 border-gray-100 dark:border-gray-700 hover:border-indigo-200 dark:hover:border-indigo-500 p-6 group"
                        >
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors mb-1">
                                        {{ log.workout_name }}
                                    </h3>
                                    <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400 transition-colors">
                                        <span class="flex items-center gap-1">
                                            📅 {{ formatDate(log.created_at) }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            🕐 {{ formatTime(log.created_at) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div 
                                        class="inline-flex items-center justify-center w-16 h-16 rounded-full font-black text-lg"
                                        :class="log.completed_sets === log.total_sets 
                                            ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400' 
                                            : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400'"
                                    >
                                        {{ Math.round((log.completed_sets / log.total_sets) * 100) }}%
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 transition-colors">
                                    <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 transition-colors">{{ log.duration_minutes || '--' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium transition-colors">Minutos</div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 transition-colors">
                                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 transition-colors">{{ log.completed_sets }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium transition-colors">Series</div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 transition-colors">
                                    <div class="text-2xl font-bold text-pink-600 dark:text-pink-400 transition-colors">{{ log.exercises_data.length }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium transition-colors">Ejercicios</div>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Empty state -->
                    <div v-else class="text-center py-16 bg-white dark:bg-gray-800 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700 transition-colors">
                        <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-4xl transition-colors">
                            📊
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 transition-colors">Aún no has completado entrenamientos</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto transition-colors">
                            Empieza una rutina en modo Live y complétala para ver tu historial aquí.
                        </p>
                        <Link 
                            :href="route('profile.show.public', { user: $page.props.auth.user.username })"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg"
                        >
                            <span>🏋️</span>
                            Ver mis rutinas
                        </Link>
                    </div>

                    <!-- Indicador de carga para scroll infinito -->
                    <div v-if="isLoadingMore" class="mt-8 flex justify-center">
                        <div class="flex items-center gap-3 px-6 py-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 transition-colors">
                            <svg class="animate-spin h-5 w-5 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm font-bold text-gray-600 dark:text-gray-300 transition-colors">Cargando más entrenamientos...</span>
                        </div>
                    </div>
                    
                    <!-- Mensaje de fin -->
                    <div v-else-if="allLogs.length > 0 && !nextPageUrl" class="mt-8 text-center">
                        <p class="text-sm text-gray-400 dark:text-gray-500 font-medium transition-colors">✨ Has visto todo tu historial</p>
                    </div>

                </div>
            </div>
        </AuthenticatedLayout>
    </div>
</template>
