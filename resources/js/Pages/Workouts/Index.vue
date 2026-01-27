<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
    workouts: Object,
    categories: Array,
    title: String,
    filters: Object
});

const search = ref(props.filters.search || '');
const isLoadingMore = ref(false);

// Debounce search to avoid too many requests
const performSearch = debounce((value) => {
    router.get(route('workouts.index'), { 
        ...props.filters, 
        search: value 
    }, { 
        preserveState: true, 
        preserveScroll: true,
        replace: true 
    });
}, 300);

watch(search, (value) => {
    performSearch(value);
});

const setDifficulty = (level) => {
    router.get(route('workouts.index'), {
        ...props.filters,
        difficulty: level === props.filters.difficulty ? null : level // Toggle
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const setCategory = (cat) => {
    router.get(route('workouts.index'), {
        ...props.filters,
        category: cat === props.filters.category ? null : cat // Toggle
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const setSort = (sortOption) => {
    router.get(route('workouts.index'), {
        ...props.filters,
        sort: sortOption
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const getGradient = (level) => {
    const gradients = {
        Principiante: 'from-emerald-400 to-teal-500',
        Intermedio: 'from-blue-500 to-indigo-600',
        Avanzado: 'from-purple-500 to-pink-600'
    };
    return gradients[level] || 'from-gray-400 to-gray-500';
};

// Scroll infinito
const loadMore = () => {
    if (isLoadingMore.value || !props.workouts.next_page_url) return;
    
    isLoadingMore.value = true;
    router.get(props.workouts.next_page_url, {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['workouts'],
        onFinish: () => {
            isLoadingMore.value = false;
        }
    });
};

const handleScroll = () => {
    const scrollPosition = window.innerHeight + window.scrollY;
    const documentHeight = document.documentElement.scrollHeight;
    
    // Cargar más cuando estemos a 300px del final
    if (scrollPosition >= documentHeight - 300) {
        loadMore();
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-md text-white">
                    <span class="text-2xl">🏋️</span>
                </div>
                <div>
                    <h2 class="font-extrabold text-xl text-gray-900 dark:text-white leading-tight transition-colors">
                        Explorar
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium transition-colors">Descubre contenido de la comunidad</p>
                </div>
            </div>
        </template>

        <!-- Tabs Navigation -->
        <div class="bg-white dark:bg-gray-800 shadow-md border-b border-gray-100 dark:border-gray-700 sticky top-16 z-30 transition-colors">
            <div class="max-w-7xl mx-auto px-4">
                <nav class="flex gap-1" aria-label="Tabs">
                    <Link :href="route('workouts.index')"
                            class="flex-1 group inline-flex items-center justify-center py-4 px-4 text-center font-bold text-sm whitespace-nowrap transition-all duration-200 rounded-t-xl relative text-indigo-600 dark:text-indigo-400 bg-gradient-to-b from-indigo-50 dark:from-indigo-900/30 to-transparent">
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        Rutinas
                        <span class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-t-full"></span>
                    </Link>
                    <Link :href="route('discover.index')"
                            class="flex-1 group inline-flex items-center justify-center py-4 px-4 text-center font-bold text-sm whitespace-nowrap transition-all duration-200 rounded-t-xl relative text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        Personas
                    </Link>
                </nav>
            </div>
        </div>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4">
                
                <!-- Buscador y Filtros -->
                <div class="mb-8 space-y-6">
                    <!-- Buscador -->
                    <div class="relative max-w-2xl mx-auto">
                        <input 
                            v-model="search"
                            type="text"
                            placeholder="Buscar rutinas o usuarios"
                            class="w-full px-6 py-4 pl-14 rounded-2xl border-2 border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-900 transition-all text-lg text-gray-700 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"
                        />
                        <svg class="absolute left-5 top-1/2 -translate-y-1/2 w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Filtros Rápidos (Chips) -->
                    <div class="flex flex-col gap-4">
                        <!-- Categorías -->
                        <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide -mx-4 px-4 justify-start sm:justify-center">
                            <!-- Botón Todas -->
                            <button 
                                @click="setCategory(null)"
                                class="px-4 py-2 rounded-full text-sm font-bold transition-all border-2 whitespace-nowrap"
                                :class="!filters.category ? 'bg-slate-700 dark:bg-slate-500 text-white border-slate-700 dark:border-slate-500 shadow-md' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-slate-300 dark:hover:border-slate-600'"
                            >
                                Todas
                            </button>

                            <button 
                                v-for="cat in categories" 
                                :key="cat.id"
                                @click="setCategory(cat.slug)"
                                class="px-4 py-2 rounded-full text-sm font-bold transition-all border-2 flex items-center gap-2 whitespace-nowrap"
                                :class="filters.category === cat.slug ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-indigo-200 dark:hover:border-indigo-800 hover:text-indigo-600 dark:hover:text-indigo-400'"
                            >
                                <span>{{ cat.icon }}</span>
                                {{ cat.name }}
                            </button>
                        </div>

                        <!-- Dificultad -->
                        <div class="flex flex-wrap justify-center gap-3">
                            <button 
                                @click="setDifficulty('Principiante')"
                                class="px-4 py-2 rounded-full text-sm font-bold transition-all border-2 flex items-center gap-2"
                                :class="filters.difficulty === 'Principiante' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-emerald-200 dark:hover:border-emerald-800 hover:text-emerald-600 dark:hover:text-emerald-400'"
                            >
                                🌱 Principiante
                            </button>
                            <button 
                                @click="setDifficulty('Intermedio')"
                                class="px-4 py-2 rounded-full text-sm font-bold transition-all border-2 flex items-center gap-2"
                                :class="filters.difficulty === 'Intermedio' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-blue-200 dark:hover:border-blue-800 hover:text-blue-600 dark:hover:text-blue-400'"
                            >
                                ⚡ Intermedio
                            </button>
                            <button 
                                @click="setDifficulty('Avanzado')"
                                class="px-4 py-2 rounded-full text-sm font-bold transition-all border-2 flex items-center gap-2"
                                :class="filters.difficulty === 'Avanzado' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 border-purple-200 dark:border-purple-800' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-purple-200 dark:hover:border-purple-800 hover:text-purple-600 dark:hover:text-purple-400'"
                            >
                                🔥 Avanzado
                            </button>
                        </div>
                    </div>

                    <!-- Ordenamiento -->
                    <div class="flex items-center justify-center gap-2">
                        <span class="text-sm font-bold text-gray-500"></span>
                        <div class="flex gap-2">
                            <button 
                                @click="setSort('popular')"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                                :class="filters.sort === 'popular' || !filters.sort ? 'bg-slate-700 dark:bg-slate-500 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'"
                            >
                                🔥 Más populares
                            </button>
                            <button 
                                @click="setSort('recent')"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                                :class="filters.sort === 'recent' ? 'bg-slate-700 dark:bg-slate-500 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'"
                            >
                                🆕 Más recientes
                            </button>
                            <button 
                                @click="setSort('exercises')"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                                :class="filters.sort === 'exercises' ? 'bg-slate-700 dark:bg-slate-500 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'"
                            >
                                📊 Más completas
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Grid de rutinas -->
                <div v-if="workouts.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <Link 
                        v-for="workout in workouts.data" 
                        :key="workout.id"
                        :href="route('workouts.show', workout.id)"
                        class="group relative bg-white dark:bg-gray-800 rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 dark:border-gray-700 flex flex-col"
                    >
                        <!-- Header Gradiente -->
                        <div class="h-24 bg-gradient-to-r relative overflow-hidden" :class="getGradient(workout.difficulty)">
                            <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <!-- Patrón decorativo opcional -->
                            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                        </div>

                        <!-- Contenido -->
                        <div class="px-6 pb-6 flex-1 flex flex-col">
                            <!-- Avatar y Categoría -->
                            <div class="flex justify-between items-end -mt-10 mb-4 relative z-10">
                                <img 
                                    :src="workout.user.profile_picture_url ? `/storage/${workout.user.profile_picture_url}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(workout.user.name)}&background=random`"
                                    class="w-16 h-16 rounded-2xl border-4 border-white dark:border-gray-800 shadow-md bg-white dark:bg-gray-700 object-cover transition-colors"
                                    :alt="workout.user.name"
                                />
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded-lg transition-colors flex items-center gap-1.5 border border-indigo-100/50 dark:border-indigo-800/50 shadow-sm">
                                    <span v-if="workout.category?.icon">{{ workout.category.icon }}</span>
                                    {{ workout.category?.name || workout.category }}
                                </span>
                            </div>

                            <!-- Pills de Dificultad y Visibilidad -->
                            <div class="flex flex-wrap gap-2 mb-3">
                                <!-- Dificultad -->
                                <span v-if="workout.difficulty" 
                                      class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-lg transition-colors"
                                      :class="{
                                          'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400': workout.difficulty === 'Principiante',
                                          'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400': workout.difficulty === 'Intermedio',
                                          'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400': workout.difficulty === 'Avanzado'
                                      }">
                                    {{ workout.difficulty }}
                                </span>
                                
                                <!-- Pública/Privada -->
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-lg transition-colors border"
                                      :class="workout.is_public 
                                          ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800' 
                                          : 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 border-rose-100 dark:border-rose-800'">
                                    {{ workout.is_public ? '🌐 Pública' : '🔒 Privada' }}
                                </span>
                            </div>

                            <!-- Título y Autor -->
                            <div class="mb-4">
                                <h3 class="text-xl font-black text-gray-900 dark:text-white leading-tight mb-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-1">
                                    {{ workout.name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium transition-colors">
                                    por <span class="text-gray-700 dark:text-gray-300">@{{ workout.user.username }}</span>
                                </p>
                            </div>

                            <!-- Stats Grid -->
                            <div class="grid grid-cols-2 gap-3 mb-6">
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-2 text-center transition-colors">
                                    <p class="text-xs text-gray-400 dark:text-gray-400 font-bold uppercase">Duración</p>
                                    <p class="font-bold text-gray-700 dark:text-white transition-colors">{{ workout.duration_minutes }} min</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-2 text-center transition-colors">
                                    <p class="text-xs text-gray-400 dark:text-gray-400 font-bold uppercase">Ejercicios</p>
                                    <p class="font-bold text-gray-700 dark:text-white transition-colors">{{ workout.exercises?.length || 0 }}</p>
                                </div>
                            </div>

                            <!-- Botón Ver -->
                            <div class="mt-auto pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between transition-colors">
                                <span class="text-xs font-bold text-indigo-500 dark:text-indigo-400 flex items-center gap-1 transition-colors">
                                    📌 {{ workout.times_saved }} guardados
                                </span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white group-hover:translate-x-1 transition-transform flex items-center gap-1">
                                    Ver Rutina
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Empty state -->
                <div v-else class="text-center py-16 bg-white dark:bg-gray-800 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700 transition-colors">
                    <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-4xl transition-colors">
                        🏋️
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 transition-colors">No hay rutinas disponibles</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto transition-colors">
                        Conecta con otros usuarios para ver sus rutinas y expandir tu red.
                    </p>
                    <Link 
                        :href="route('discover.index')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 dark:bg-indigo-500 text-white rounded-xl font-bold hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-colors shadow-lg"
                    >
                        <span>👥</span>
                        Buscar GymPals
                    </Link>
                </div>

                <!-- Indicador de carga para scroll infinito -->
                <div v-if="isLoadingMore" class="mt-8 flex justify-center">
                    <div class="flex items-center gap-3 px-6 py-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 transition-colors">
                        <svg class="animate-spin h-5 w-5 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm font-bold text-gray-600 dark:text-gray-300 transition-colors">Cargando más rutinas...</span>
                    </div>
                </div>

                <!-- Mensaje de fin -->
                <div v-else-if="workouts.data.length > 0 && !workouts.next_page_url" class="mt-8 text-center">
                    <p class="text-sm text-gray-400 font-medium">✨ Has visto todas las rutinas disponibles</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
