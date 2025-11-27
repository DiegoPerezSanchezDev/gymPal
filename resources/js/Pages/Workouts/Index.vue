<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
    workouts: Object,
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
        principiante: 'from-emerald-400 to-teal-500',
        intermedio: 'from-blue-500 to-indigo-600',
        avanzado: 'from-purple-500 to-pink-600'
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
                    <h2 class="font-extrabold text-xl text-gray-900 leading-tight">
                        Explorar
                    </h2>
                    <p class="text-xs text-gray-500 font-medium">Descubre contenido de la comunidad</p>
                </div>
            </div>
        </template>

        <!-- Tabs Navigation -->
        <div class="bg-white shadow-md border-b border-gray-100 sticky top-16 z-30">
            <div class="max-w-7xl mx-auto px-4">
                <nav class="flex gap-1" aria-label="Tabs">
                    <Link :href="route('workouts.index')"
                            class="flex-1 group inline-flex items-center justify-center py-4 px-4 text-center font-bold text-sm whitespace-nowrap transition-all duration-200 rounded-t-xl relative text-indigo-600 bg-gradient-to-b from-indigo-50 to-transparent">
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        Rutinas
                        <span class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-t-full"></span>
                    </Link>
                    <Link :href="route('discover.index')"
                            class="flex-1 group inline-flex items-center justify-center py-4 px-4 text-center font-bold text-sm whitespace-nowrap transition-all duration-200 rounded-t-xl relative text-gray-500 hover:text-gray-700 hover:bg-gray-50">
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
                            class="w-full px-6 py-4 pl-14 rounded-2xl border-2 border-gray-100 bg-white shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all text-lg text-gray-700 placeholder-gray-400"
                        />
                        <svg class="absolute left-5 top-1/2 -translate-y-1/2 w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Filtros Rápidos (Chips) -->
                    <div class="flex flex-wrap justify-center gap-3">
                        <button 
                            @click="setDifficulty('principiante')"
                            class="px-4 py-2 rounded-full text-sm font-bold transition-all border-2 flex items-center gap-2"
                            :class="filters.difficulty === 'principiante' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-white text-gray-500 border-gray-200 hover:border-emerald-200 hover:text-emerald-600'"
                        >
                            🌱 Principiante
                        </button>
                        <button 
                            @click="setDifficulty('intermedio')"
                            class="px-4 py-2 rounded-full text-sm font-bold transition-all border-2 flex items-center gap-2"
                            :class="filters.difficulty === 'intermedio' ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-white text-gray-500 border-gray-200 hover:border-blue-200 hover:text-blue-600'"
                        >
                            ⚡ Intermedio
                        </button>
                        <button 
                            @click="setDifficulty('avanzado')"
                            class="px-4 py-2 rounded-full text-sm font-bold transition-all border-2 flex items-center gap-2"
                            :class="filters.difficulty === 'avanzado' ? 'bg-purple-100 text-purple-700 border-purple-200' : 'bg-white text-gray-500 border-gray-200 hover:border-purple-200 hover:text-purple-600'"
                        >
                            🔥 Avanzado
                        </button>
                    </div>

                    <!-- Ordenamiento -->
                    <div class="flex items-center justify-center gap-2">
                        <span class="text-sm font-bold text-gray-500"></span>
                        <div class="flex gap-2">
                            <button 
                                @click="setSort('popular')"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                                :class="filters.sort === 'popular' || !filters.sort ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            >
                                🔥 Más populares
                            </button>
                            <button 
                                @click="setSort('recent')"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                                :class="filters.sort === 'recent' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            >
                                🆕 Más recientes
                            </button>
                            <button 
                                @click="setSort('exercises')"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                                :class="filters.sort === 'exercises' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
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
                        class="group relative bg-white rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 flex flex-col"
                    >
                        <!-- Header Gradiente -->
                        <div class="h-24 bg-gradient-to-r relative overflow-hidden" :class="getGradient(workout.difficulty_level)">
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
                                    class="w-16 h-16 rounded-2xl border-4 border-white shadow-md bg-white object-cover"
                                    :alt="workout.user.name"
                                />
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 bg-gray-100 text-gray-600 rounded-lg">
                                    {{ workout.category }}
                                </span>
                            </div>

                            <!-- Título y Autor -->
                            <div class="mb-4">
                                <h3 class="text-xl font-black text-gray-900 leading-tight mb-1 group-hover:text-indigo-600 transition-colors line-clamp-1">
                                    {{ workout.name }}
                                </h3>
                                <p class="text-sm text-gray-500 font-medium">
                                    por <span class="text-gray-700">@{{ workout.user.username }}</span>
                                </p>
                            </div>

                            <!-- Stats Grid -->
                            <div class="grid grid-cols-2 gap-3 mb-6">
                                <div class="bg-gray-50 rounded-xl p-2 text-center">
                                    <p class="text-xs text-gray-400 font-bold uppercase">Duración</p>
                                    <p class="font-bold text-gray-700">{{ workout.duration_minutes }} min</p>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-2 text-center">
                                    <p class="text-xs text-gray-400 font-bold uppercase">Ejercicios</p>
                                    <p class="font-bold text-gray-700">{{ workout.exercises?.length || 0 }}</p>
                                </div>
                            </div>

                            <!-- Botón Ver -->
                            <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                                <span class="text-xs font-bold text-indigo-500 flex items-center gap-1">
                                    📌 {{ workout.times_saved }} guardados
                                </span>
                                <span class="text-sm font-bold text-gray-900 group-hover:translate-x-1 transition-transform flex items-center gap-1">
                                    Ver Rutina
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Empty state -->
                <div v-else class="text-center py-16 bg-white rounded-2xl border-2 border-dashed border-gray-200">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-4xl">
                        🏋️
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No hay rutinas disponibles</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">
                        Sé el primero en crear una rutina pública para la comunidad.
                    </p>
                    <Link 
                        :href="route('workouts.create')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg"
                    >
                        <span>➕</span>
                        Crear Rutina
                    </Link>
                </div>

                <!-- Indicador de carga para scroll infinito -->
                <div v-if="isLoadingMore" class="mt-8 flex justify-center">
                    <div class="flex items-center gap-3 px-6 py-3 bg-white rounded-xl shadow-sm border border-gray-100">
                        <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm font-bold text-gray-600">Cargando más rutinas...</span>
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
