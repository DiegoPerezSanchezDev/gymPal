<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import WorkoutPreviewCard from '@/Components/WorkoutPreviewCard.vue';

const props = defineProps({
    workouts: Object,
    totalPublic: Number,
    totalPrivate: Number,
    title: String,
    filters: Object
});

const visibilityFilter = ref(props.filters?.visibility || 'all');
const categoryFilter = ref(props.filters?.category || '');
const difficultyFilter = ref(props.filters?.difficulty || '');
const showFilters = ref(false);

const categories = [
    '🏋️ Gym', 
    '🤸 Calistenia', 
    '🏃 Running', 
    '🚴 Ciclismo', 
    '🧘 Yoga',
    '⚽ Deportes de equipo',
    '🥊 Artes Marciales',
    '🏊 Natación',
    '💪 Otro'
];

const applyFilters = () => {
    router.get(route('workouts.my-workouts'), {
        visibility: visibilityFilter.value,
        category: categoryFilter.value,
        difficulty: difficultyFilter.value
    }, {
        preserveState: true,
        replace: true
    });
};

const clearFilters = () => {
    visibilityFilter.value = 'all';
    categoryFilter.value = '';
    difficultyFilter.value = '';
    applyFilters();
};

const totalWorkouts = computed(() => props.totalPublic + props.totalPrivate);

const hasActiveFilters = computed(() => {
    return visibilityFilter.value !== 'all' || categoryFilter.value !== '' || difficultyFilter.value !== '';
});
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <div class="pb-4">
            <div class="px-4 pt-4">
                
                <!-- Header con Stats -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h1 class="text-2xl font-black text-gray-900">Mis Rutinas</h1>
                            <p class="text-sm text-gray-500">{{ totalWorkouts }} rutinas creadas</p>
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
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-4 border border-green-200">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xl">🌐</span>
                                <div class="text-2xl font-black text-green-600">{{ totalPublic }}</div>
                            </div>
                            <div class="text-xs text-gray-600 font-medium">Públicas</div>
                        </div>
                        <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-2xl p-4 border border-gray-200">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xl">🔒</span>
                                <div class="text-2xl font-black text-gray-700">{{ totalPrivate }}</div>
                            </div>
                            <div class="text-xs text-gray-600 font-medium">Privadas</div>
                        </div>
                    </div>
                </div>

                <!-- Filtros Horizontales (Chips) -->
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-3">
                        <h3 class="text-sm font-bold text-gray-700">Filtros</h3>
                        <button 
                            v-if="hasActiveFilters"
                            @click="clearFilters"
                            class="text-xs font-bold text-indigo-600 active:text-indigo-800"
                        >
                            Limpiar
                        </button>
                    </div>

                    <!-- Visibilidad -->
                    <div class="flex gap-2 overflow-x-auto pb-2 -mx-4 px-4 scrollbar-hide mb-3">
                        <button 
                            @click="visibilityFilter = 'all'; applyFilters()"
                            class="flex-shrink-0 px-4 py-2 rounded-full font-bold text-sm transition active:scale-95"
                            :class="visibilityFilter === 'all' 
                                ? 'bg-indigo-600 text-white shadow-md' 
                                : 'bg-gray-100 text-gray-700'"
                        >
                            Todas
                        </button>
                        <button 
                            @click="visibilityFilter = 'public'; applyFilters()"
                            class="flex-shrink-0 px-4 py-2 rounded-full font-bold text-sm transition active:scale-95"
                            :class="visibilityFilter === 'public' 
                                ? 'bg-green-600 text-white shadow-md' 
                                : 'bg-gray-100 text-gray-700'"
                        >
                            🌐 Públicas
                        </button>
                        <button 
                            @click="visibilityFilter = 'private'; applyFilters()"
                            class="flex-shrink-0 px-4 py-2 rounded-full font-bold text-sm transition active:scale-95"
                            :class="visibilityFilter === 'private' 
                                ? 'bg-gray-700 text-white shadow-md' 
                                : 'bg-gray-100 text-gray-700'"
                        >
                            🔒 Privadas
                        </button>
                    </div>

                    <!-- Categorías -->
                    <div class="flex gap-2 overflow-x-auto pb-2 -mx-4 px-4 scrollbar-hide mb-3">
                        <button 
                            @click="categoryFilter = ''; applyFilters()"
                            class="flex-shrink-0 px-4 py-2 rounded-full font-bold text-sm transition active:scale-95"
                            :class="categoryFilter === '' 
                                ? 'bg-purple-600 text-white shadow-md' 
                                : 'bg-gray-100 text-gray-700'"
                        >
                            Todas
                        </button>
                        <button 
                            v-for="cat in categories" 
                            :key="cat"
                            @click="categoryFilter = cat; applyFilters()"
                            class="flex-shrink-0 px-4 py-2 rounded-full font-bold text-sm transition active:scale-95 whitespace-nowrap"
                            :class="categoryFilter === cat 
                                ? 'bg-purple-600 text-white shadow-md' 
                                : 'bg-gray-100 text-gray-700'"
                        >
                            {{ cat }}
                        </button>
                    </div>

                    <!-- Dificultad -->
                    <div class="flex gap-2 overflow-x-auto pb-2 -mx-4 px-4 scrollbar-hide">
                        <button 
                            @click="difficultyFilter = ''; applyFilters()"
                            class="flex-shrink-0 px-4 py-2 rounded-full font-bold text-sm transition active:scale-95"
                            :class="difficultyFilter === '' 
                                ? 'bg-orange-600 text-white shadow-md' 
                                : 'bg-gray-100 text-gray-700'"
                        >
                            Todas
                        </button>
                        <button 
                            @click="difficultyFilter = 'principiante'; applyFilters()"
                            class="flex-shrink-0 px-4 py-2 rounded-full font-bold text-sm transition active:scale-95"
                            :class="difficultyFilter === 'principiante' 
                                ? 'bg-emerald-600 text-white shadow-md' 
                                : 'bg-gray-100 text-gray-700'"
                        >
                            Principiante
                        </button>
                        <button 
                            @click="difficultyFilter = 'intermedio'; applyFilters()"
                            class="flex-shrink-0 px-4 py-2 rounded-full font-bold text-sm transition active:scale-95"
                            :class="difficultyFilter === 'intermedio' 
                                ? 'bg-blue-600 text-white shadow-md' 
                                : 'bg-gray-100 text-gray-700'"
                        >
                            Intermedio
                        </button>
                        <button 
                            @click="difficultyFilter = 'avanzado'; applyFilters()"
                            class="flex-shrink-0 px-4 py-2 rounded-full font-bold text-sm transition active:scale-95"
                            :class="difficultyFilter === 'avanzado' 
                                ? 'bg-purple-600 text-white shadow-md' 
                                : 'bg-gray-100 text-gray-700'"
                        >
                            Avanzado
                        </button>
                    </div>
                </div>

                <!-- Workouts Grid -->
                <div v-if="workouts.data.length > 0" class="space-y-4">
                    <WorkoutPreviewCard 
                        v-for="workout in workouts.data" 
                        :key="workout.id"
                        :workout="workout"
                    />
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-16 px-4">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-4xl">
                        📚
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">
                        {{ hasActiveFilters 
                            ? 'No hay rutinas con estos filtros' 
                            : 'Aún no has creado rutinas' 
                        }}
                    </h3>
                    <p class="text-gray-500 mb-6 text-sm">
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
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gray-600 text-white rounded-xl font-bold active:scale-95 transition shadow-lg"
                    >
                        Limpiar Filtros
                    </button>
                </div>

                <!-- Pagination -->
                <div v-if="workouts.links && workouts.links.length > 3" class="mt-6 flex justify-center gap-2 flex-wrap">
                    <Link 
                        v-for="(link, index) in workouts.links" 
                        :key="index"
                        :href="link.url"
                        v-html="link.label"
                        class="min-w-[44px] min-h-[44px] flex items-center justify-center px-3 rounded-lg font-bold transition active:scale-95"
                        :class="link.active 
                            ? 'bg-indigo-600 text-white shadow-md' 
                            : link.url 
                                ? 'bg-white text-gray-700 border border-gray-200' 
                                : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                    />
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
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
