<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import WorkoutPreviewCard from '@/Components/WorkoutPreviewCard.vue';
import axios from 'axios';

const props = defineProps({
    workouts: Object,
    totalPublic: Number,
    totalPrivate: Number,
    title: String,
    filters: Object
});

const visibilityFilter = ref(props.filters?.visibility || 'all');
const difficultyFilter = ref(props.filters?.difficulty || '');

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
                difficulty: difficultyFilter.value
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
        difficulty: difficultyFilter.value
    }, {
        preserveState: true,
        replace: true
    });
};

const clearFilters = () => {
    visibilityFilter.value = 'all';
    difficultyFilter.value = '';
    applyFilters();
};

const totalWorkouts = computed(() => props.totalPublic + props.totalPrivate);

const hasActiveFilters = computed(() => {
    return visibilityFilter.value !== 'all' || difficultyFilter.value !== '';
});

// Eliminar rutina
const showDeleteModal = ref(false);
const workoutToDelete = ref(null);

const confirmDelete = (workout) => {
    workoutToDelete.value = workout;
    showDeleteModal.value = true;
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    workoutToDelete.value = null;
};

const deleteWorkout = () => {
    if (!workoutToDelete.value) return;
    
    router.delete(route('workouts.destroy', workoutToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            workoutToDelete.value = null;
        }
    });
};
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

                    <!-- Workouts Grid (Scroll Infinito) -->
                    <div v-if="allWorkouts.length > 0" class="space-y-4">
                        <div v-for="workout in allWorkouts" :key="workout.id" class="relative group">
                            <WorkoutPreviewCard :workout="workout" />
                            
                            <!-- Botón Eliminar (Absoluto sobre la tarjeta) -->
                            <button 
                                @click.stop="confirmDelete(workout)"
                                class="absolute top-4 right-4 p-2 bg-white/90 backdrop-blur-sm rounded-full text-red-500 shadow-sm opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-50 z-10"
                                title="Eliminar rutina"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
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

                    <!-- Indicador de carga para scroll infinito -->
                    <div v-if="isLoadingMore" class="mt-8 flex justify-center pb-8">
                        <div class="flex items-center gap-3 px-6 py-3 bg-white rounded-xl shadow-sm border border-gray-100">
                            <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm font-bold text-gray-600">Cargando más rutinas...</span>
                        </div>
                    </div>
                    
                    <!-- Mensaje de fin de lista -->
                    <div v-else-if="allWorkouts.length > 0 && !nextPageUrl" class="mt-8 text-center pb-8">
                        <p class="text-sm text-gray-400 font-medium">✨ Has visto todas tus rutinas</p>
                    </div>

                </div>
            </div>

            <!-- Modal de Confirmación de Eliminación -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="cancelDelete"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                            Eliminar rutina
                                        </h3>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500">
                                                ¿Estás seguro de que quieres eliminar la rutina "{{ workoutToDelete?.name }}"? Esta acción no se puede deshacer.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button 
                                    type="button" 
                                    class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                                    @click="deleteWorkout"
                                >
                                    Eliminar
                                </button>
                                <button 
                                    type="button" 
                                    class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                    @click="cancelDelete"
                                >
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
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
