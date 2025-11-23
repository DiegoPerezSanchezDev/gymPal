<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    logs: Object,
    title: String
});

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

const totalWorkouts = computed(() => props.logs.total || 0);
const totalMinutes = computed(() => {
    return props.logs.data.reduce((sum, log) => sum + (log.duration_minutes || 0), 0);
});
const totalSets = computed(() => {
    return props.logs.data.reduce((sum, log) => sum + log.completed_sets, 0);
});
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-md">
                        <span class="text-2xl">📊</span>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-xl text-gray-900 leading-tight">
                            Historial de Entrenamientos
                        </h2>
                        <p class="text-xs text-gray-500 font-medium">Tu progreso y dedicación</p>
                    </div>
                </div>
                <Link 
                    :href="route('workout-logs.calendar')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition text-sm flex items-center gap-2"
                >
                    📅 Calendario
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto px-4">
                
                <!-- Estadísticas generales -->
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-8 text-white shadow-lg mb-8 text-center">
                    <div class="text-6xl font-black mb-2">{{ totalWorkouts }}</div>
                    <div class="text-lg text-indigo-100">Entrenamientos Completados</div>
                    <p class="text-sm text-indigo-200 mt-2">¡Sigue así! 💪</p>
                </div>

                <!-- Lista de entrenamientos -->
                <div v-if="logs.data.length > 0" class="space-y-4">
                    <Link 
                        v-for="log in logs.data" 
                        :key="log.id"
                        :href="route('workout-logs.show', log.id)"
                        class="block bg-white rounded-2xl shadow-md hover:shadow-xl transition-all border-2 border-gray-100 hover:border-indigo-200 p-6 group"
                    >
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-900 group-hover:text-indigo-600 transition-colors mb-1">
                                    {{ log.workout_name }}
                                </h3>
                                <div class="flex items-center gap-4 text-sm text-gray-500">
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
                                        ? 'bg-green-100 text-green-600' 
                                        : 'bg-yellow-100 text-yellow-600'"
                                >
                                    {{ Math.round((log.completed_sets / log.total_sets) * 100) }}%
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <div class="text-2xl font-bold text-indigo-600">{{ log.duration_minutes || '--' }}</div>
                                <div class="text-xs text-gray-500 font-medium">Minutos</div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <div class="text-2xl font-bold text-purple-600">{{ log.completed_sets }}</div>
                                <div class="text-xs text-gray-500 font-medium">Series</div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <div class="text-2xl font-bold text-pink-600">{{ log.exercises_data.length }}</div>
                                <div class="text-xs text-gray-500 font-medium">Ejercicios</div>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Empty state -->
                <div v-else class="text-center py-16 bg-white rounded-2xl border-2 border-dashed border-gray-200">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-4xl">
                        📊
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Aún no has completado entrenamientos</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">
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

                <!-- Paginación -->
                <div v-if="logs.links && logs.links.length > 3" class="mt-8 flex justify-center gap-2">
                    <Link 
                        v-for="(link, index) in logs.links" 
                        :key="index"
                        :href="link.url"
                        v-html="link.label"
                        class="px-4 py-2 rounded-lg font-bold transition"
                        :class="link.active 
                            ? 'bg-indigo-600 text-white' 
                            : link.url 
                                ? 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' 
                                : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                    />
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
