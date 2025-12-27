<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import WorkoutPreviewCard from '@/Components/WorkoutPreviewCard.vue';
import axios from 'axios';

const props = defineProps({
    workouts: Object,
    categories: Array,
    totalPublic: Number,
    totalPrivate: Number,
    title: String,
    filters: Object
});

const visibilityFilter = ref(props.filters?.visibility || 'all');
const difficultyFilter = ref(props.filters?.difficulty || '');
const categoryFilter = ref(props.filters?.category || '');

// Scroll Infinito
const allWorkouts = ref([...props.workouts.data]);
const isLoadingMore = ref(false);
const nextPageUrl = ref(props.workouts.next_page_url);

// Actualizar lista cuando cambian los filtros (desde el servidor)
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
            },
            params: {
                visibility: visibilityFilter.value,
                difficulty: difficultyFilter.value,
                category: categoryFilter.value
            }
        });
        
        // Manejar respuesta JSON de Laravel
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

const applyFilters = () => {
    router.get(route('workouts.my-workouts'), {
        visibility: visibilityFilter.value,
        difficulty: difficultyFilter.value,
        category: categoryFilter.value
    }, {
        preserveState: true,
        replace: true
    });
};

const clearFilters = () => {
    visibilityFilter.value = 'all';
    difficultyFilter.value = '';
    categoryFilter.value = '';
    applyFilters();
};

const totalWorkouts = computed(() => props.totalPublic + props.totalPrivate);

const hasActiveFilters = computed(() => {
    return visibilityFilter.value !== 'all' || difficultyFilter.value !== '' || categoryFilter.value !== '';
});
</script>

<template>
    <div>
        <Head :title="title" />

        <AuthenticatedLayout>
            <div class="pb-4">
                <div class="px-4 pt-4">
                    
                    <!-- Header con Stats -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h1 class="text-2xl font-black text-gray-900 dark:text-white transition-colors">Mis Rutinas</h1>
                                <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors">{{ totalWorkouts }} rutinas creadas</p>
                            </div>
                            <Link 
                                :href="route('workouts.create')"
                                class="px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full flex items-center gap-2 text-white font-bold shadow-lg active:scale-95 transition"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="text-sm">Nueva</span>
                            </Link>
                        </div>

                        <!-- Stats Compactas -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-4 border border-green-200 dark:border-green-800 transition-colors">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xl">🌐</span>
                                    <div class="text-2xl font-black text-green-600 dark:text-green-400 transition-colors">{{ totalPublic }}</div>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 font-medium transition-colors">Públicas</div>
                            </div>
                            <div class="bg-gradient-to-br from-gray-50 to-slate-50 dark:from-gray-800 dark:to-slate-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 transition-colors">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xl">🔒</span>
                                    <div class="text-2xl font-black text-gray-700 dark:text-gray-200 transition-colors">{{ totalPrivate }}</div>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 font-medium transition-colors">Privadas</div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros Horizontales (Chips) -->
                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-3">
                            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 transition-colors">Filtros</h3>
                            <button 
                                v-if="hasActiveFilters"
                                @click="clearFilters"
                                class="text-xs font-bold text-indigo-600 dark:text-indigo-400 active:text-indigo-800 dark:active:text-indigo-300 transition-colors"
                            >
                                Limpiar
                            </button>
                        </div>

                        <!-- Categorías -->
                        <div class="flex gap-2 overflow-x-auto pb-2 -mx-4 px-4 scrollbar-hide mb-3">
                            <button 
                                @click="categoryFilter = ''; applyFilters()"
                                class="flex-shrink-0 px-4 py-2 rounded-full font-bold text-sm transition active:scale-95"
                                :class="categoryFilter === '' 
                                    ? 'bg-indigo-600 text-white shadow-md' 
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                            >
                                Todas
                            </button>
                            <button 
                                v-for="cat in categories"
                                :key="cat.id"
                                @click="categoryFilter = cat.slug; applyFilters()"
                                class="flex-shrink-0 px-4 py-2 rounded-full font-bold text-sm transition active:scale-95 flex items-center gap-2"
                                :class="categoryFilter === cat.slug 
                                    ? 'bg-indigo-600 text-white shadow-md' 
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                            >
                                <span>{{ cat.icon }}</span>
                                {{ cat.name }}
                            </button>
                        </div>

                        <!-- Visibilidad -->
                        <div class="flex gap-2 overflow-x-auto pb-2 -mx-4 px-4 scrollbar-hide mb-3">
                            <button 
                                @click="visibilityFilter = 'all'; applyFilters()"
                                class="flex-shrink-0 px-4 py-2 rounded-full font-bold text-sm transition active:scale-95"
                                :class="visibilityFilter === 'all' 
                                    ? 'bg-indigo-600 text-white shadow-md' 
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                            >
                                Todas
                            </button>
                            <button 
                                @click="visibilityFilter = 'public'; applyFilters()"
                                class="flex-shrink-0 px-4 py-2 rounded-full font-bold text-sm transition active:scale-95"
                                :class="visibilityFilter === 'public' 
                                    ? 'bg-green-600 text-white shadow-md' 
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                            >
                                🌐 Públicas
                            </button>
                            <button 
                                @click="visibilityFilter = 'private'; applyFilters()"
                                class="flex-shrink-0 px-4 py-2 rounded-full font-bold text-sm transition active:scale-95"
                                :class="visibilityFilter === 'private' 
                                    ? 'bg-gray-700 dark:bg-gray-600 text-white shadow-md' 
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                            >
                                🔒 Privadas
                            </button>
                        </div>

                        <!-- Dificultad -->
                        <div class="flex gap-2 overflow-x-auto pb-2 -mx-4 px-4 scrollbar-hide">
                            <button 
                                @click="difficultyFilter = ''; applyFilters()"
                                class="flex-shrink-0 px-4 py-2 rounded-full font-bold text-sm transition active:scale-95"
                                :class="difficultyFilter === '' 
                                    ? 'bg-orange-600 text-white shadow-md' 
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                            >
                                Todas
                            </button>
                            <button 
                                @click="difficultyFilter = 'principiante'; applyFilters()"
                                class="flex-shrink-0 px-4 py-2 rounded-full font-bold text-sm transition active:scale-95"
                                :class="difficultyFilter === 'principiante' 
                                    ? 'bg-emerald-600 text-white shadow-md' 
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                            >
                                Principiante
                            </button>
                            <button 
                                @click="difficultyFilter = 'intermedio'; applyFilters()"
                                class="flex-shrink-0 px-4 py-2 rounded-full font-bold text-sm transition active:scale-95"
                                :class="difficultyFilter === 'intermedio' 
                                    ? 'bg-blue-600 text-white shadow-md' 
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                            >
                                Intermedio
                            </button>
                            <button 
                                @click="difficultyFilter = 'avanzado'; applyFilters()"
                                class="flex-shrink-0 px-4 py-2 rounded-full font-bold text-sm transition active:scale-95"
                                :class="difficultyFilter === 'avanzado' 
                                    ? 'bg-purple-600 text-white shadow-md' 
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                            >
                                Avanzado
                            </button>
                        </div>
                    </div>

                    <!-- Workouts Grid (Scroll Infinito) -->
                    <div v-if="allWorkouts.length > 0" class="space-y-4">
                        <div v-for="workout in allWorkouts" :key="workout.id" class="relative group">
                            <WorkoutPreviewCard :workout="workout" />
                            

                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-16 px-4">
                        <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-4xl transition-colors">
                            📚
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 transition-colors">
                            {{ hasActiveFilters 
                                ? 'No hay rutinas con estos filtros' 
                                : 'Aún no has creado rutinas' 
                            }}
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6 text-sm transition-colors">
                            {{ hasActiveFilters 
                                ? 'Intenta cambiar los filtros para ver más resultados.' 
                                : 'Crea tu primera rutina de entrenamiento para empezar.' 
                            }}
                        </p>
                        <Link 
                            v-if="!hasActiveFilters"
                            :href="route('workouts.create')"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold active:scale-95 transition shadow-lg"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Crear Primera Rutina
                        </Link>
                        <button 
                            v-else
                            @click="clearFilters"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-gray-600 dark:bg-gray-700 text-white rounded-xl font-bold active:scale-95 transition shadow-lg"
                        >
                            Limpiar Filtros
                        </button>
                    </div>

                    <!-- Indicador de carga para scroll infinito -->
                    <div v-if="isLoadingMore" class="mt-8 flex justify-center pb-8">
                        <div class="flex items-center gap-3 px-6 py-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 transition-colors">
                            <svg class="animate-spin h-5 w-5 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm font-bold text-gray-600 dark:text-gray-300 transition-colors">Cargando más rutinas...</span>
                        </div>
                    </div>
                    
                    <!-- Mensaje de fin de lista -->
                    <div v-else-if="allWorkouts.length > 0 && !nextPageUrl" class="mt-8 text-center pb-8">
                        <p class="text-sm text-gray-400 dark:text-gray-500 font-medium transition-colors">✨ Has visto todas tus rutinas</p>
                    </div>

                </div>
            </div>


        </AuthenticatedLayout>
    </div>
</template>

<style scoped>
/* Ocultar scrollbar pero mantener funcionalidad */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
