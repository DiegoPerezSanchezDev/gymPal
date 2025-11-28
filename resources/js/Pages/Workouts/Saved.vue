<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    workouts: Object,
    title: String
});

const searchQuery = ref('');

// Scroll Infinito
const allWorkouts = ref([...props.workouts.data]);
const isLoadingMore = ref(false);
const nextPageUrl = ref(props.workouts.next_page_url);

// Actualizar lista cuando cambian los props
watch(() => props.workouts, (newWorkouts) => {
    allWorkouts.value = [...newWorkouts.data];
    nextPageUrl.value = newWorkouts.next_page_url;
}, { deep: true });

const loadMoreItems = async () => {
    if (isLoadingMore.value || !nextPageUrl.value) return;

    isLoadingMore.value = true;

    try {
        const res = await axios.get(nextPageUrl.value, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = res.data;
        const newWorkouts = data.workouts || data;
        
        if (newWorkouts && newWorkouts.data) {
            allWorkouts.value = [...allWorkouts.value, ...newWorkouts.data];
            nextPageUrl.value = newWorkouts.next_page_url;
        }
    } catch (err) {
        console.error("Error cargando más rutinas", err);
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

const filteredWorkouts = computed(() => {
    if (!searchQuery.value) return allWorkouts.value;
    
    const query = searchQuery.value.toLowerCase();
    return allWorkouts.value.filter(workout => 
        workout.name.toLowerCase().includes(query) ||
        workout.user.name.toLowerCase().includes(query) ||
        workout.user.username.toLowerCase().includes(query)
    );
});

const getGradient = (level) => {
    const gradients = {
        Principiante: 'from-emerald-400 to-teal-500',
        Intermedio: 'from-blue-500 to-indigo-600',
        Avanzado: 'from-purple-500 to-pink-600'
    };
    return gradients[level] || 'from-gray-400 to-gray-500';
};
</script>

<template>
    <div>
        <Head :title="title" />

        <AuthenticatedLayout>
            <template #header>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl flex items-center justify-center shadow-md text-white">
                        <span class="text-2xl">📌</span>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-xl text-gray-900 leading-tight">
                            Rutinas Guardadas
                        </h2>
                        <p class="text-xs text-gray-500 font-medium">Tus rutinas favoritas</p>
                    </div>
                </div>
            </template>

            <div class="py-8">
                <div class="max-w-7xl mx-auto px-4">
                    
                    <!-- Buscador -->
                    <div class="mb-8">
                        <div class="relative max-w-md mx-auto md:mx-0">
                            <input 
                                v-model="searchQuery"
                                type="text"
                                placeholder="Buscar por nombre o creador"
                                class="w-full px-5 py-3 pl-12 rounded-2xl border-none bg-white shadow-sm focus:ring-2 focus:ring-pink-500 transition text-gray-700 placeholder-gray-400"
                            />
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Grid de rutinas -->
                    <div v-if="filteredWorkouts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <Link 
                            v-for="workout in filteredWorkouts" 
                            :key="workout.id"
                            :href="route('workouts.show', workout.id)"
                            class="group relative bg-white rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 flex flex-col"
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
                                        class="w-16 h-16 rounded-2xl border-4 border-white shadow-md bg-white object-cover"
                                        :alt="workout.user.name"
                                    />
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 bg-gray-100 text-gray-600 rounded-lg">
                                        {{ workout.category }}
                                    </span>
                                </div>

                                <!-- Título y Autor -->
                                <div class="mb-4">
                                    <h3 class="text-xl font-black text-gray-900 leading-tight mb-1 group-hover:text-pink-600 transition-colors line-clamp-1">
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
                                    <span class="text-xs font-bold text-pink-500 flex items-center gap-1">
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
                        <div class="w-20 h-20 bg-pink-50 rounded-full flex items-center justify-center mx-auto mb-4 text-4xl">
                            📌
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No has guardado ninguna rutina</h3>
                        <p class="text-gray-500 mb-6 max-w-md mx-auto">
                            Explora rutinas de la comunidad y guarda tus favoritas para acceder rápidamente.
                        </p>
                        <Link 
                            :href="route('workouts.index')"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-pink-600 text-white rounded-xl font-bold hover:bg-pink-700 transition shadow-lg"
                        >
                            <span>🔍</span>
                            Explorar Rutinas
                        </Link>
                    </div>

                    <!-- Indicador de carga para scroll infinito -->
                    <div v-if="isLoadingMore" class="mt-8 flex justify-center">
                        <div class="flex items-center gap-3 px-6 py-3 bg-white rounded-xl shadow-sm border border-gray-100">
                            <svg class="animate-spin h-5 w-5 text-pink-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm font-bold text-gray-600">Cargando más rutinas...</span>
                        </div>
                    </div>
                    
                    <!-- Mensaje de fin -->
                    <div v-else-if="allWorkouts.length > 0 && !nextPageUrl && !searchQuery" class="mt-8 text-center">
                        <p class="text-sm text-gray-400 font-medium">✨ Has visto todas tus rutinas guardadas</p>
                    </div>

                </div>
            </div>
        </AuthenticatedLayout>
    </div>
</template>
