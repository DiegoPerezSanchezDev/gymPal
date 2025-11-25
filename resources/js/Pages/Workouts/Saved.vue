<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    workouts: Object,
    title: String
});

const searchQuery = ref('');

const filteredWorkouts = computed(() => {
    if (!searchQuery.value) return props.workouts.data;
    
    const query = searchQuery.value.toLowerCase();
    return props.workouts.data.filter(workout => 
        workout.name.toLowerCase().includes(query) ||
        workout.user.name.toLowerCase().includes(query) ||
        workout.user.username.toLowerCase().includes(query)
    );
});

const getGradient = (level) => {
    const gradients = {
        principiante: 'from-emerald-400 to-teal-500',
        intermedio: 'from-blue-500 to-indigo-600',
        avanzado: 'from-purple-500 to-pink-600'
    };
    return gradients[level] || 'from-gray-400 to-gray-500';
};
</script>

<template>
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
                            placeholder="Buscar por nombre o creador..."
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

                <!-- Paginación -->
                <div v-if="workouts.links && workouts.links.length > 3" class="mt-8 flex justify-center gap-2">
                    <Link 
                        v-for="(link, index) in workouts.links" 
                        :key="index"
                        :href="link.url"
                        v-html="link.label"
                        class="px-4 py-2 rounded-lg font-bold transition"
                        :class="link.active 
                            ? 'bg-pink-600 text-white' 
                            : link.url 
                                ? 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' 
                                : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                    />
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
