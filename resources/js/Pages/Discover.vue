<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UserCardSkeleton from '@/Components/Skeletons/UserCardSkeleton.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, watch, onMounted, computed } from 'vue';
import SelectInput from '@/Components/SelectInput.vue';
import MultiSelectInput from '@/Components/MultiSelectInput.vue';
import _ from 'lodash';

const props = defineProps({
    title: String,
    users: Object, // Paginación de usuarios
    filters: Object,
    user: Object, // Usuario actual
    interests: Array,
    searchedInterestId: [String, Number],
});

const experienceOptions = [
    { value: '', label: '✨ Todos los niveles' },
    { value: 'Principiante', label: '🌱 Principiante' },
    { value: 'Intermedio', label: '⚡ Intermedio' },
    { value: 'Avanzado', label: '🔥 Avanzado' }
];

const interestOptions = computed(() => {
    const opts = props.interests.map(i => ({ value: i.id, label: i.name }));
    return [{ value: '', label: 'Todos los deportes' }, ...opts];
});

// Opciones para MultiSelect de deportes
const interestsMultiOptions = computed(() => {
    return props.interests.map(i => ({ value: i.id, label: i.name }));
});

// Opciones para MultiSelect de disponibilidad
const availabilityOptions = [
    { value: 'Mañana', label: 'Mañana' },
    { value: 'Tarde', label: 'Tarde' },
    { value: 'Noche', label: 'Noche' },
    { value: 'Finde semana', label: 'Finde semana' }
];

const form = useForm({
    search: props.filters.search || '',
    city: props.filters.city || '',
    interests: props.filters.interests || [],
    availability_general: props.filters.availability_general || [],
    experience_level: props.filters.experience_level || '',
    looking_for_interest_id: props.filters.looking_for_interest_id || '',
    interest_id: props.filters.interest_id || (props.user?.looking_for_interest_id || ''), // Deporte para buscar compañero
    filtro_rapido: props.filters.filtro_rapido || '',
    lat: props.filters.lat || null,
    lon: props.filters.lon || null,
});

const showFilters = ref(false);
const isLoading = ref(true);

onMounted(() => {
    // Delay de 1.5 segundos para dar tiempo a que se oculten datos anteriores
    if (props.users) {
        setTimeout(() => isLoading.value = false, 1500);
    }
});


const submit = _.debounce(() => {
    form.get(route('discover.index'), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onFinish: () => {
            // Delay para mostrar skeleton y dar tiempo a transición
            setTimeout(() => isLoading.value = false, 800);
        },
    });
}, 500);

// Watches que activan loading inmediatamente
watch(() => form.search, () => { isLoading.value = true; submit(); });
watch(() => form.city, () => { isLoading.value = true; submit(); });
watch(() => form.interests, () => { isLoading.value = true; submit(); });
watch(() => form.availability_general, () => { isLoading.value = true; submit(); });
watch(() => form.experience_level, () => { isLoading.value = true; submit(); });
watch(() => form.interest_id, () => { isLoading.value = true; submit(); });
watch(() => form.looking_for_interest_id, () => { isLoading.value = true; submit(); });

const aplicarFiltroRapido = (tipo) => {
    form.filtro_rapido = form.filtro_rapido === tipo ? '' : tipo; // Toggle
    
    // Limpiar otros filtros si se activa uno rápido (opcional, depende de la UX deseada)
    if (form.filtro_rapido) {
        form.search = '';
        form.city = '';
        form.interests = [];
        form.availability_general = [];
        form.experience_level = '';
        form.looking_for_interest_id = '';
        // No reseteamos lat/lon si es 'cerca de mi'
        if (tipo !== 'cerca_de_mi') {
            form.lat = null;
            form.lon = null;
        }
        // Si es 'buscando_companero', usar el deporte del usuario actual por defecto
        if (tipo === 'buscando_companero' && props.user?.looking_for_interest_id) {
            form.interest_id = props.user.looking_for_interest_id;
        }
    } else {
        // Si se desactiva el filtro, limpiar también el interest_id
        form.interest_id = '';
    }
    submit();
};

const buscarPorUbicacion = () => {
    if (!navigator.geolocation) {
        alert("La geolocalización no es soportada por tu navegador.");
        return;
    }

    navigator.geolocation.getCurrentPosition((position) => {
        form.lat = position.coords.latitude;
        form.lon = position.coords.longitude;
        form.filtro_rapido = ''; // Desactivar filtros rápidos si se usa geo explícito
        submit();
    }, () => {
        alert("No se pudo obtener tu ubicación.");
    });
};

const limpiarFiltros = () => {
    form.reset();
    form.interests = []; // Reset manual para arrays
    form.availability_general = [];
    submit();
};

// Helper para color de afinidad
const getAffinityColor = (score) => {
    if (score >= 80) return 'from-green-400 to-emerald-600';
    if (score >= 50) return 'from-yellow-400 to-orange-500';
    return 'from-blue-400 to-indigo-500';
};

const availabilitySlots = ['Mañanas', 'Tardes', 'Noches', 'Fines de semana'];
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
                            class="flex-1 group inline-flex items-center justify-center py-4 px-4 text-center font-bold text-sm whitespace-nowrap transition-all duration-200 rounded-t-xl relative text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        Rutinas
                    </Link>
                    <Link :href="route('discover.index')"
                            class="flex-1 group inline-flex items-center justify-center py-4 px-4 text-center font-bold text-sm whitespace-nowrap transition-all duration-200 rounded-t-xl relative text-indigo-600 dark:text-indigo-400 bg-gradient-to-b from-indigo-50 dark:from-indigo-900/30 to-transparent">
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        Personas
                        <span class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-t-full"></span>
                    </Link>
                </nav>
            </div>
        </div>

        <div class="py-6 md:py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Barra de Herramientas y Filtros -->
                <div class="bg-white dark:bg-gray-800 shadow-lg sm:rounded-xl p-6 mb-8 border border-gray-100 dark:border-gray-700 transition-colors">
                    <!-- Buscador Principal -->
                    <div class="relative mb-6">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input 
                            v-model="form.search" 
                            type="text" 
                            placeholder="Buscar por nombre o usuario" 
                            class="pl-10 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-colors"
                        >
                    </div>

                    <!-- Filtros Rápidos (Chips) -->
                    <div class="flex flex-wrap gap-3 mb-6">
                        <button 
                            @click="aplicarFiltroRapido('mas_activos')"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border"
                            :class="form.filtro_rapido === 'mas_activos' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-700 shadow-sm' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'"
                        >
                            🔥 Más Activos
                        </button>
                        <button 
                            @click="aplicarFiltroRapido('nuevos_en_ciudad')"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border"
                            :class="form.filtro_rapido === 'nuevos_en_ciudad' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-700 shadow-sm' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'"
                        >
                            🏙️ Nuevos en mi ciudad
                        </button>
                        <button 
                            @click="aplicarFiltroRapido('buscando_companero')"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border"
                            :class="form.filtro_rapido === 'buscando_companero' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-700 shadow-sm' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'"
                        >
                            🤝 Buscar compañero
                        </button>
                        <button 
                            @click="buscarPorUbicacion"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border"
                            :class="form.lat ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 border-green-200 dark:border-green-700 shadow-sm' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'"
                        >
                            📍 Cerca de mí
                        </button>
                        
                        <button 
                            @click="showFilters = !showFilters"
                            class="ml-auto text-sm text-indigo-600 dark:text-indigo-400 font-semibold hover:text-indigo-800 dark:hover:text-indigo-300 flex items-center gap-1 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                            {{ showFilters ? 'Ocultar Filtros' : 'Más Filtros' }}
                        </button>
                    </div>

                    <!-- Filtros Avanzados (Collapsible) -->
                    <div v-show="showFilters" class="pt-6 border-t border-gray-100 dark:border-gray-700 animate-fade-in-down space-y-6 transition-colors">
                        
                        <!-- Fila 1: Nivel y Ciudad -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <SelectInput
                                    id="experience_level"
                                    label="💪 Nivel de Experiencia"
                                    v-model="form.experience_level"
                                    :options="experienceOptions"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-2 transition-colors">
                                    <span class="text-base">📍</span>
                                    <span>Ciudad</span>
                                </label>
                                <input v-model="form.city" type="text" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 sm:text-sm font-medium bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-colors" placeholder="Ej: Madrid, Barcelona...">
                            </div>
                        </div>
                        
                        <!-- Intereses (MultiSelect) -->
                        <div>
                            <MultiSelectInput
                                id="interests"
                                label="🏃 Deportes que practican"
                                v-model="form.interests"
                                :options="interestsMultiOptions"
                                placeholder="Seleccionar deportes..."
                            />
                        </div>

                        <!-- Disponibilidad (MultiSelect) -->
                        <div>
                            <MultiSelectInput
                                id="availability"
                                label="⏰ Disponibilidad"
                                v-model="form.availability_general"
                                :options="availabilityOptions"
                                placeholder="Seleccionar horarios..."
                            />
                        </div>

                        <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-700 transition-colors">
                            <button @click="limpiarFiltros" class="text-sm text-red-500 hover:text-red-700 font-bold flex items-center gap-1 hover:gap-2 transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                Limpiar filtros
                            </button>
                        </div>
                    </div>

                    <!-- Filtro Específico para "Buscan Compañero" -->
                    <div v-if="form.filtro_rapido === 'buscando_companero'" class="mt-6 pt-6 border-t border-indigo-100 dark:border-indigo-800 bg-indigo-50/30 dark:bg-indigo-900/20 -mx-6 px-6 pb-6 rounded-b-xl transition-colors">
                        <label class="block text-sm font-bold text-indigo-700 mb-3 flex items-center gap-2">
                            <span class="text-lg">🤝</span>
                            <span>Buscar compañero para</span>
                        </label>
                        <SelectInput
                            id="interest_id"
                            v-model="form.interest_id"
                            :options="interestOptions"
                        />
                        <p class="text-xs text-indigo-600 mt-2 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                            Las tarjetas que coincidan se resaltarán automáticamente
                        </p>
                    </div>
                </div>

                <!-- Grid de Resultados -->
                <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <UserCardSkeleton v-for="n in 6" :key="n" />
                </div>

                <div v-else>
                    <div v-if="users.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="user in users.data" :key="user.id" 
                             class="bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col h-full group relative transform scale-[0.98]"
                             :class="{
                                 'border-2 border-indigo-500 ring-2 ring-indigo-200 dark:ring-indigo-900': form.filtro_rapido === 'buscando_companero' && form.interest_id && user.looking_for_interest_id == form.interest_id,
                                 'border border-gray-100 dark:border-gray-700': !(form.filtro_rapido === 'buscando_companero' && form.interest_id && user.looking_for_interest_id == form.interest_id)
                             }">
                            
                            <!-- Badge de Nivel de Experiencia (Top Left) -->
                            <div v-if="user.experience_level" 
                                 class="absolute top-3 left-3 text-white text-[10px] font-bold px-2 py-1 rounded-full shadow-sm z-10 flex items-center gap-1"
                                 :class="{
                                     'bg-green-600': user.experience_level === 'Principiante',
                                     'bg-blue-600': user.experience_level === 'Intermedio',
                                     'bg-purple-600': user.experience_level === 'Avanzado'
                                 }">
                                <span>{{ user.experience_level === 'Principiante' ? '🌱' : (user.experience_level === 'Intermedio' ? '⚡' : '🔥') }}</span>
                                <span>{{ user.experience_level }}</span>
                            </div>

                            <!-- Badge de Afinidad (Top Right) -->
                            <div v-if="user.affinity_score > 0" 
                                 class="absolute top-3 right-3 text-white text-[10px] font-bold px-2 py-1 rounded-full shadow-sm z-10 bg-gradient-to-r"
                                 :class="getAffinityColor(user.affinity_score)"
                            >
                                {{ user.affinity_score }}% Afinidad
                            </div>

                            <!-- Header / Avatar -->
                            <div class="p-6 flex flex-col items-center flex-grow">
                                
                                <div class="relative mb-4">
                                    <img 
                                        :src="user.profile_picture_url ? '/storage/' + user.profile_picture_url : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&background=random&color=fff'" 
                                        :alt="user.name" 
                                        class="w-24 h-24 rounded-full object-cover border-4 border-white dark:border-gray-700 shadow-md group-hover:scale-105 transition-transform duration-300"
                                    >
                                    <span v-if="user.connection_status === 'accepted'" class="absolute bottom-1 right-1 bg-green-500 w-5 h-5 rounded-full border-2 border-white" title="Conectado"></span>
                                </div>
                                
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white text-center mt-2 transition-colors">{{ user.name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-3 transition-colors">@{{ user.username }}</p>
                                
                                <!-- Ubicación y Distancia -->
                                <div class="flex items-center justify-center gap-2 mb-4 text-xs text-gray-500 dark:text-gray-400 transition-colors">
                                    <span v-if="user.location_city" class="flex items-center">
                                        📍 {{ user.location_city }}
                                    </span>
                                    <span v-if="user.distance !== undefined && user.distance !== null" class="flex items-center font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-1.5 py-0.5 rounded transition-colors">
                                        📏 A {{ parseFloat(user.distance).toFixed(2) }} km
                                    </span>
                                </div>

                                <!-- Separador -->
                                <div class="w-full border-t border-gray-100 dark:border-gray-700 my-2 transition-colors"></div>

                                <!-- Buscando Compañero (solo visible cuando el filtro está activo) -->
                                <div v-if="form.filtro_rapido === 'buscando_companero' && user.looking_for_interest" class="w-full mb-3">
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-wider font-bold mb-2 text-center transition-colors">Buscando compañero</p>
                                    <div class="flex justify-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border-2 shadow-sm"
                                              :class="{
                                                  'bg-indigo-100 text-indigo-700 border-indigo-500 shadow-indigo-200': form.interest_id && user.looking_for_interest_id == form.interest_id,
                                                  'bg-purple-50 text-purple-700 border-purple-200': !form.interest_id || user.looking_for_interest_id != form.interest_id
                                              }">
                                            🤝 {{ user.looking_for_interest.name }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Deportes / Intereses -->
                                <div class="w-full mt-2">
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-wider font-bold mb-2 text-center transition-colors">Practica</p>
                                    <div class="flex flex-wrap justify-center gap-1.5">
                                        <span v-for="interest in user.fitness_interests.slice(0, 3)" :key="interest.id" 
                                              class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-700 shadow-sm transition-colors">
                                            {{ interest.name }}
                                        </span>
                                        <span v-if="user.fitness_interests.length > 3" class="text-[10px] text-gray-400 dark:text-gray-500 font-bold flex items-center justify-center bg-gray-50 dark:bg-gray-700 rounded-full border border-gray-100 dark:border-gray-600 h-6 w-6 transition-colors">
                                            +{{ user.fitness_interests.length - 3 }}
                                        </span>
                                    </div>
                                </div>


                                <!-- Disponibilidad (Si existe) -->
                                <div v-if="user.availability_general && user.availability_general.length" class="w-full mt-4">
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-wider font-bold mb-2 text-center transition-colors">Disponible</p>
                                    <div class="flex flex-wrap justify-center gap-1">
                                        <span v-for="slot in user.availability_general.slice(0, 2)" :key="slot" class="text-[10px] px-1.5 py-0.5 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded border border-green-100 dark:border-green-800 transition-colors">
                                            {{ slot }}
                                        </span>
                                        <span v-if="user.availability_general.length > 2" class="text-[10px] text-gray-400">...</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer / Actions -->
                            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-t border-gray-100 dark:border-gray-700 mt-auto transition-colors">
                                <button 
                                    @click="router.visit(route('profile.show.public', { user: user.username }))"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm hover:shadow focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform active:scale-95"
                                >
                                    Ver Perfil
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-16">
                        <div class="mx-auto h-24 w-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 transition-colors">
                            <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white transition-colors">No se encontraron usuarios</h3>
                        <p class="mt-1 text-gray-500 dark:text-gray-400 transition-colors">Intenta ajustar tus filtros de búsqueda.</p>
                        <button @click="limpiarFiltros" class="mt-4 text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-semibold transition-colors">
                            Limpiar filtros
                        </button>
                    </div>
                </div>

                <!-- Paginación -->
                <div v-if="!isLoading && users.links.length > 3" class="mt-8 flex justify-center">
                     <template v-for="(link, key) in users.links" :key="key">
                        <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded" v-html="link.label" />
                        <Link v-else class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500" :class="{ 'bg-indigo-500 text-white': link.active }" :href="link.url" v-html="link.label" preserve-scroll />
                    </template>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-fade-in-down {
    animation: fadeInDown 0.3s ease-out;
}
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>