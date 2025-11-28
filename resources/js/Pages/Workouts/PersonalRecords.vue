<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    records: Array,
    title: String
});

const searchQuery = ref('');

const filteredRecords = computed(() => {
    if (!searchQuery.value) return props.records;
    
    return props.records.filter(record => 
        record.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const sortedRecords = computed(() => {
    return [...filteredRecords.value].sort((a, b) => 
        b.max_volume - a.max_volume
    );
});
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl flex items-center justify-center shadow-md">
                    <span class="text-2xl">🏆</span>
                </div>
                <div>
                    <h2 class="font-extrabold text-xl text-gray-900 dark:text-white leading-tight transition-colors">
                        Récords Personales
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium transition-colors">Tus mejores marcas por ejercicio</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-6xl mx-auto px-4">
                
                <!-- Buscador -->
                <div class="mb-8">
                    <div class="relative max-w-md">
                        <input 
                            v-model="searchQuery"
                            type="text"
                            placeholder="Buscar ejercicio..."
                            class="w-full px-4 py-3 pl-12 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900 outline-none transition-all"
                        />
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Grid de récords -->
                <div v-if="sortedRecords.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="(record, index) in sortedRecords" 
                        :key="record.name"
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border-2 border-gray-100 dark:border-gray-700 hover:border-yellow-300 dark:hover:border-yellow-500 transition-all p-6 relative overflow-hidden group"
                    >
                        <!-- Medalla para top 3 -->
                        <div 
                            v-if="index < 3"
                            class="absolute top-4 right-4 text-4xl"
                        >
                            {{ index === 0 ? '🥇' : index === 1 ? '🥈' : '🥉' }}
                        </div>

                        <!-- Nombre del ejercicio -->
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 pr-12 transition-colors">
                            {{ record.name }}
                        </h3>

                        <!-- Estadísticas -->
                        <div class="space-y-3">
                            <!-- Peso máximo -->
                            <div class="flex items-center justify-between p-3 bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/30 dark:to-pink-900/30 rounded-lg transition-colors">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">💪</span>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300 transition-colors">Peso máximo</span>
                                </div>
                                <div class="text-2xl font-black text-red-600 dark:text-red-400 transition-colors">
                                    {{ record.max_weight }} kg
                                </div>
                            </div>

                            <!-- Reps máximas -->
                            <div class="flex items-center justify-between p-3 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-lg transition-colors">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">🔢</span>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300 transition-colors">Reps máximas</span>
                                </div>
                                <div class="text-2xl font-black text-blue-600 dark:text-blue-400 transition-colors">
                                    {{ record.max_reps }}
                                </div>
                            </div>

                            <!-- Volumen máximo -->
                            <div class="flex items-center justify-between p-3 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/30 dark:to-pink-900/30 rounded-lg transition-colors">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">📊</span>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300 transition-colors">Volumen máx</span>
                                </div>
                                <div class="text-2xl font-black text-purple-600 dark:text-purple-400 transition-colors">
                                    {{ record.max_volume }}
                                </div>
                            </div>
                        </div>

                        <!-- Sesiones totales -->
                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 transition-colors">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400 font-medium transition-colors">Sesiones registradas</span>
                                <span class="font-bold text-gray-700 dark:text-gray-200 transition-colors">{{ record.total_sessions }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else-if="!searchQuery" class="text-center py-16 bg-white dark:bg-gray-800 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700 transition-colors">
                    <div class="w-20 h-20 bg-yellow-50 dark:bg-yellow-900/30 rounded-full flex items-center justify-center mx-auto mb-4 text-4xl transition-colors">
                        🏆
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 transition-colors">Aún no tienes récords registrados</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto transition-colors">
                        Completa entrenamientos para empezar a rastrear tus récords personales.
                    </p>
                    <Link 
                        :href="route('workout-logs.index')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg"
                    >
                        <span>📊</span>
                        Ver historial
                    </Link>
                </div>

                <!-- No results -->
                <div v-else class="text-center py-16 bg-white dark:bg-gray-800 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700 transition-colors">
                    <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-4xl transition-colors">
                        🔍
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 transition-colors">No se encontraron ejercicios</h3>
                    <p class="text-gray-500 dark:text-gray-400 transition-colors">
                        Intenta con otro término de búsqueda
                    </p>
                </div>

                <!-- Info adicional -->
                <div v-if="sortedRecords.length > 0" class="mt-8 bg-indigo-50 dark:bg-indigo-900/20 border-2 border-indigo-100 dark:border-indigo-800 rounded-2xl p-6 transition-colors">
                    <div class="flex items-start gap-4">
                        <div class="text-3xl">💡</div>
                        <div>
                            <h4 class="font-bold text-indigo-900 dark:text-indigo-300 mb-2 transition-colors">¿Cómo se calculan los récords?</h4>
                            <ul class="text-sm text-indigo-700 dark:text-indigo-400 space-y-1 transition-colors">
                                <li><strong>Peso máximo:</strong> El mayor peso levantado en una serie completada</li>
                                <li><strong>Reps máximas:</strong> El mayor número de repeticiones en una serie</li>
                                <li><strong>Volumen máximo:</strong> El mayor resultado de peso × reps en una serie</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
