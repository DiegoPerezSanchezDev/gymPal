<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PostCard from '@/Components/PostCard.vue';

const props = defineProps({
    workouts: Object,
    savedPosts: Object,
    title: String
});

const activeTab = ref('workouts');
const searchQuery = ref('');

const switchTab = (tab) => {
    activeTab.value = tab;
};

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
                        <h2 class="font-extrabold text-xl text-gray-900 dark:text-white leading-tight transition-colors">
                            Guardados
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium transition-colors">Tus publicaciones y rutinas favoritas</p>
                    </div>
                </div>
            </template>

            <div class="py-8">
                <div class="max-w-7xl mx-auto px-4">
                    
                    <!-- Tabs -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6 transition-colors">
                        <div class="flex">
                            <button
                                @click="switchTab('workouts')"
                                class="flex-1 px-6 py-4 font-bold text-sm transition-all"
                                :class="activeTab === 'workouts' 
                                    ? 'bg-pink-600 text-white' 
                                    : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'"
                            >
                                🏋️ Rutinas ({{ workouts.data.length }})
                            </button>
                            <button
                                @click="switchTab('posts')"
                                class="flex-1 px-6 py-4 font-bold text-sm transition-all"
                                :class="activeTab === 'posts' 
                                    ? 'bg-pink-600 text-white' 
                                    : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'"
                            >
                                📄 Posts ({{ savedPosts?.data?.length || 0 }})
                            </button>
                        </div>
                    </div>

                    <!-- Rutinas Tab -->
                    <div v-if="activeTab === 'workouts'">
                        <!-- Buscador -->
                        <div class="mb-8">
                            <div class="relative max-w-md mx-auto md:mx-0">
                                <input 
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Buscar por nombre o creador"
                                    class="w-full px-5 py-3 pl-12 rounded-2xl border-none bg-white dark:bg-gray-800 shadow-sm focus:ring-2 focus:ring-pink-500 transition-colors text-gray-700 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500"
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
                                class="group relative bg-white dark:bg-gray-800 rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 dark:border-gray-700 flex flex-col"
                            >
                                <!-- Header Gradiente -->
                                <div class="h-24 bg-gradient-to-r relative overflow-hidden" :class="getGradient(workout.difficulty)">
                                    <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                                </div>

                                <!-- Contenido -->
                                <div class="px-6 pb-6 flex-1 flex flex-col">
                                    <div class="flex justify-between items-end -mt-10 mb-4 relative z-10">
                                        <img 
                                            :src="workout.user.profile_picture_url ? `/storage/${workout.user.profile_picture_url}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(workout.user.name)}&background=random`"
                                            class="w-16 h-16 rounded-2xl border-4 border-white shadow-md bg-white object-cover"
                                            :alt="workout.user.name"
                                        />
                                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg transition-colors">
                                            {{ workout.category }}
                                        </span>
                                    </div>

                                    <div class="mb-4">
                                        <h3 class="text-xl font-black text-gray-900 dark:text-white leading-tight mb-1 group-hover:text-pink-600 dark:group-hover:text-pink-400 transition-colors line-clamp-1">
                                            {{ workout.name }}
                                        </h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium transition-colors">
                                            por <span class="text-gray-700 dark:text-gray-300">@{{ workout.user.username }}</span>
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3 mb-6">
                                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-2 text-center transition-colors">
                                            <p class="text-xs text-gray-400 dark:text-gray-500 font-bold uppercase transition-colors">Duración</p>
                                            <p class="font-bold text-gray-700 dark:text-gray-200 transition-colors">{{ workout.duration_minutes }} min</p>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-2 text-center transition-colors">
                                            <p class="text-xs text-gray-400 dark:text-gray-500 font-bold uppercase transition-colors">Ejercicios</p>
                                            <p class="font-bold text-gray-700 dark:text-gray-200 transition-colors">{{ workout.exercises?.length || 0 }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-auto pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between transition-colors">
                                        <span class="text-xs font-bold text-pink-500 dark:text-pink-400 flex items-center gap-1 transition-colors">
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

                        <!-- Empty state rutinas -->
                        <div v-else class="text-center py-16 bg-white dark:bg-gray-800 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700 transition-colors">
                            <div class="w-20 h-20 bg-pink-50 dark:bg-pink-900/30 rounded-full flex items-center justify-center mx-auto mb-4 text-4xl transition-colors">
                                📌
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 transition-colors">No has guardado ninguna rutina</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto transition-colors">
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
                    </div>

                    <!-- Posts Tab -->
                    <div v-if="activeTab === 'posts'" class="space-y-6">
                        <div v-if="savedPosts && savedPosts.data && savedPosts.data.length > 0" class="space-y-6">
                            <PostCard 
                                v-for="post in savedPosts.data" 
                                :key="post.id" 
                                :post="post"
                            />
                        </div>
                        <div v-else class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border-2 border-dashed border-gray-200 dark:border-gray-700 p-12 text-center transition-colors">
                            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 transition-colors">
                                <svg class="w-10 h-10 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                            </div>
                            <p class="text-gray-900 dark:text-white font-bold text-lg transition-colors">No tienes posts guardados</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-4 transition-colors">Guarda posts interesantes para verlos más tarde</p>
                            <Link 
                                :href="route('feed.index')" 
                                class="inline-flex items-center gap-2 px-6 py-3 bg-pink-600 text-white rounded-xl font-bold hover:bg-pink-700 transition shadow-lg"
                            >
                                <span>📰</span>
                                Explorar Posts
                            </Link>
                        </div>
                    </div>

                </div>
            </div>
        </AuthenticatedLayout>
    </div>
</template>
