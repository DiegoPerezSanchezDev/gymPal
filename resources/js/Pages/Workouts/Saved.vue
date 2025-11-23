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

function getDifficultyColor(level) {
    const colors = {
        principiante: 'bg-emerald-100 text-emerald-700',
        intermedio: 'bg-indigo-100 text-indigo-700',
        avanzado: 'bg-purple-100 text-purple-700'
    };
    return colors[level] || 'bg-gray-100 text-gray-700';
}
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl flex items-center justify-center shadow-md">
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
                <div class="mb-6">
                    <div class="relative max-w-md">
                        <input 
                            v-model="searchQuery"
                            type="text"
                            placeholder="Buscar por nombre o creador..."
                            class="w-full px-4 py-3 pl-12 rounded-xl border-2 border-gray-200 focus:border-pink-500 focus:ring-2 focus:ring-pink-200 outline-none transition"
                        />
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
                
                <!-- Grid de rutinas -->
                <div v-if="filteredWorkouts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link 
                        v-for="workout in filteredWorkouts" 
                        :key="workout.id"
                        :href="route('workouts.show', workout.id)"
                        class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-all border-2 border-gray-100 hover:border-pink-200 overflow-hidden group"
                    >
                        <!-- Header con usuario -->
                        <div class="p-4 border-b border-gray-100 bg-gray-50">
                            <div class="flex items-center gap-2">
                                <img 
                                    :src="workout.user.profile_picture_url ? `/storage/${workout.user.profile_picture_url}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(workout.user.name)}&background=random`"
                                    class="w-8 h-8 rounded-full"
                                    :alt="workout.user.name"
                                />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-900 truncate">{{ workout.user.name }}</p>
                                    <p class="text-xs text-gray-500">@{{ workout.user.username }}</p>
                                </div>
                                <span class="text-pink-500">📌</span>
                            </div>
                        </div>

                        <!-- Contenido -->
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="text-lg font-bold text-gray-900 group-hover:text-pink-600 transition-colors flex-1 pr-2">
                                    {{ workout.name }}
                                </h3>
                                <span 
                                    :class="['px-2 py-1 rounded-full text-xs font-bold capitalize whitespace-nowrap', getDifficultyColor(workout.difficulty_level)]"
                                >
                                    {{ workout.difficulty_level }}
                                </span>
                            </div>
                            
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                {{ workout.description }}
                            </p>

                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                <span class="flex items-center gap-1">
                                    ⏱️ {{ workout.duration_minutes }} min
                                </span>
                                <span class="flex items-center gap-1">
                                    💪 {{ workout.exercises?.length || 0 }} ejercicios
                                </span>
                            </div>

                            <!-- Stats -->
                            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                                <span>{{ workout.category }}</span>
                                <span class="flex items-center gap-1">
                                    📌 {{ workout.times_saved }} guardados
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
