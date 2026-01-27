<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UserCardSkeleton from '@/Components/Skeletons/UserCardSkeleton.vue';
import GymDetailsModal from '@/Components/GymDetailsModal.vue';
import GymMembersModal from '@/Components/GymMembersModal.vue';
import CityAutocomplete from '@/Components/CityAutocomplete.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, watch, onMounted, computed } from 'vue';
import SelectInput from '@/Components/SelectInput.vue';
import MultiSelectInput from '@/Components/MultiSelectInput.vue';
import _ from 'lodash';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LMarker, LPopup, LIcon, LCircle } from "@vue-leaflet/vue-leaflet";

// Mapping colors for sport interests
const getInterestColorClass = (name) => {
    const n = name.toLowerCase();
    if (n.includes('gym') || n.includes('pesasm') || n.includes('bodybuilding')) return 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border-indigo-100 dark:border-indigo-800';
    if (n.includes('calistenia')) return 'bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 border-emerald-100 dark:border-emerald-800';
    if (n.includes('crossfit')) return 'bg-orange-50 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300 border-orange-100 dark:border-orange-800';
    if (n.includes('yoga') || n.includes('pilates')) return 'bg-purple-50 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 border-purple-100 dark:border-purple-800';
    if (n.includes('running') || n.includes('ciclismo')) return 'bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border-blue-100 dark:border-blue-800';
    if (n.includes('boxeo') || n.includes('artes marciales') || n.includes('mma')) return 'bg-red-50 dark:bg-red-900/40 text-red-700 dark:text-red-300 border-red-100 dark:border-red-800';
    if (n.includes('natación')) return 'bg-cyan-50 dark:bg-cyan-900/40 text-cyan-700 dark:text-cyan-300 border-cyan-100 dark:border-cyan-800';
    if (n.includes('baile') || n.includes('zumba')) return 'bg-pink-50 dark:bg-pink-900/40 text-pink-700 dark:text-pink-300 border-pink-100 dark:border-pink-800';
    return 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-100 dark:border-gray-700'; 
};

const props = defineProps({
    title: String,
    users: Object, // Paginación de usuarios
    filters: Object,
    user: Object, // Usuario actual
    interests: Array,
    searchedInterestId: [String, Number],
    initialGyms: Array,
    gymsInCity: Array,
    geoapify_key: String,
});

// State
const viewMode = ref('grid');
const zoom = ref(14);
const showFilters = ref(false);
const isLoading = ref(true);
const showLocationPermissionModal = ref(false);
const intentAfterLocation = ref('grid'); // 'grid' o 'map'

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
    gym_id: props.filters.gym_id || '',
});

// Options
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

const interestsMultiOptions = computed(() => {
    return props.interests.map(i => ({ value: i.id, label: i.name }));
});

const gymsInCityOptions = computed(() => {
    if (!props.gymsInCity || props.gymsInCity.length === 0) return [];
    return [
        { value: '', label: 'Cualquier gimnasio' }, 
        ...props.gymsInCity.map(g => ({ 
            value: g.id, 
            label: g.address ? `${g.name} - ${g.address}` : g.name 
        }))
    ];
});

const availabilityOptions = [
    { value: 'Mañana', label: 'Mañana' },
    { value: 'Tarde', label: 'Tarde' },
    { value: 'Noche', label: 'Noche' },
    { value: 'Finde semana', label: 'Finde semana' }
];

// Map Logic & Helpers
const usersWithLocation = computed(() => {
    if (!props.users?.data) return [];
    return props.users.data.filter(u => u.latitude && u.longitude);
});

const mapCenter = computed(() => {
    if (form.lat && form.lon) return [form.lat, form.lon];
    if (usersWithLocation.value.length > 0) {
        return [parseFloat(usersWithLocation.value[0].latitude), parseFloat(usersWithLocation.value[0].longitude)];
    }
    return [40.4168, -3.7038]; // Madrid por defecto
});

const getAffinityColorClass = (score) => {
    if (score >= 80) return 'border-emerald-500 ring-2 ring-emerald-100';
    if (score >= 50) return 'border-yellow-400 ring-2 ring-yellow-100';
    return 'border-indigo-500 ring-2 ring-indigo-50';
};

// Gyms Logic (Local DB)
const gyms = ref([]);

// Initialize with props if available
if (props.initialGyms && props.initialGyms.length > 0) {
    gyms.value = props.initialGyms.map(mapGymBackendToFrontend);
}

function mapGymBackendToFrontend(gym) {
    let icon = '🏋️'; 
    let colorClass = 'border-indigo-500';
    let typeLabel = 'Gimnasio';

    switch(gym.type) {
        case 'yoga': icon = '🧘'; colorClass = 'border-pink-500'; typeLabel = 'Yoga/Pilates'; break;
        case 'crossfit': icon = '🔥'; colorClass = 'border-orange-500'; typeLabel = 'CrossFit'; break;
        case 'park': icon = '🤸'; colorClass = 'border-emerald-500'; typeLabel = 'Calistenia/Parque'; break;
        case 'pool': icon = '🏊'; colorClass = 'border-cyan-500'; typeLabel = 'Piscina'; break;
        default: icon = '🏋️'; colorClass = 'border-indigo-500'; typeLabel = 'Gimnasio';
    }

    return {
        id: gym.id,
        lat: gym.latitude,
        lon: gym.longitude,
        name: gym.name,
        type: typeLabel,
        icon,
        colorClass,
        address: gym.address,
        website: gym.website,
        hours: gym.meta_data?.hours,
        users_count: gym.users_count
    };
}

// Modal Logic
const selectedGymId = ref(null);
const showGymModal = ref(false);
const showGymMembersModal = ref(false);
const selectedGymForMembers = ref(null);

const openGymModal = (gymId) => {
    selectedGymId.value = gymId;
    showGymModal.value = true;
};

const openGymMembersModal = (gym) => {
    selectedGymForMembers.value = gym;
    showGymMembersModal.value = true;
};

const updateGymData = (updatedGym) => {
    const index = gyms.value.findIndex(g => g.id === updatedGym.id);
    if (index !== -1) {
        gyms.value[index].users_count = updatedGym.users_count;
        // Optional: Could toggle visual "is member" state on marker if we tracked it locally
    }
};

const fetchGyms = async (bounds) => {
    if (!bounds) return;

    try {
        const south = bounds.getSouth();
        const west = bounds.getWest();
        const north = bounds.getNorth();
        const east = bounds.getEast();
        
        const response = await axios.get(route('discover.gyms'), {
            params: { south, west, north, east }
        });

        gyms.value = response.data.map(mapGymBackendToFrontend);
    } catch (e) {
        console.warn("Fallo carga gyms backend", e);
    }
};

// Evento al mover el mapa
const onMapReady = (mapObject) => {
    // Carga inicial basada en la vista
    fetchGyms(mapObject.getBounds());
};

const onMapMoveEnd = (e) => {
    // Recargar al terminar de mover
    fetchGyms(e.target.getBounds());
};

const allUsers = ref(props.users.data);
const nextPageUrl = ref(props.users.next_page_url);
const isLoadingMore = ref(false);
const loadMoreTrigger = ref(null);

// Lifecycle
onMounted(() => {
    if (props.users) setTimeout(() => isLoading.value = false, 1000);

    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && nextPageUrl.value && !isLoadingMore.value && !isLoading.value) {
            loadMoreUsers();
        }
    }, { threshold: 0.1 });

    if (loadMoreTrigger.value) {
        observer.observe(loadMoreTrigger.value);
    }
});

watch(() => props.users, (newUsers) => {
    if (newUsers.current_page === 1) {
        allUsers.value = newUsers.data;
    } else {
        // En caso de navegación directa a páginas > 1 (poco común aquí)
        allUsers.value = newUsers.data;
    }
    nextPageUrl.value = newUsers.next_page_url;
});

function loadMoreUsers() {
    if (!nextPageUrl.value || isLoadingMore.value) return;

    isLoadingMore.value = true;
    router.get(nextPageUrl.value, {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            allUsers.value = [...allUsers.value, ...page.props.users.data];
            nextPageUrl.value = page.props.users.next_page_url;
            isLoadingMore.value = false;
        },
        onFinish: () => isLoadingMore.value = false
    });
}
// (Eliminamos watcher de lat/lon antiguo porque ahora va por evento de mapa)

// Actions
const submit = _.debounce(() => {
    form.get(route('discover.index'), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onFinish: () => setTimeout(() => isLoading.value = false, 800),
    });
}, 500);

// Watches
watch(() => form.search, () => { isLoading.value = true; submit(); });
watch(() => form.city, () => { isLoading.value = true; submit(); });
watch(() => form.interests, () => { isLoading.value = true; submit(); });
watch(() => form.availability_general, () => { isLoading.value = true; submit(); });
watch(() => form.experience_level, () => { isLoading.value = true; submit(); });
watch(() => form.interest_id, () => { isLoading.value = true; submit(); });
watch(() => form.looking_for_interest_id, () => { isLoading.value = true; submit(); });
watch(() => form.gym_id, () => { isLoading.value = true; submit(); });

const aplicarFiltroRapido = (tipo) => {
    form.filtro_rapido = form.filtro_rapido === tipo ? '' : tipo; // Toggle
    
    // Si se activa un filtro rápido, ocultamos el mapa y volvemos a la lista
    if (form.filtro_rapido) {
        viewMode.value = 'grid';
        form.search = ''; form.city = ''; form.interests = []; form.availability_general = []; form.experience_level = ''; form.looking_for_interest_id = '';
        if (tipo !== 'cerca_de_mi') { form.lat = null; form.lon = null; }
        if (tipo === 'buscando_companero' && props.user?.looking_for_interest_id) {
            form.interest_id = props.user.looking_for_interest_id;
        }
    } else {
        form.interest_id = '';
    }
    isLoading.value = true;
    submit();
};

const buscarPorUbicacion = () => {
    // Si ya está activo el filtro en el form, lo desactivamos (Toggle)
    if (form.lat || form.lon) {
        form.lat = null;
        form.lon = null;
        isLoading.value = true;
        submit();
        return;
    }

    if (!navigator.geolocation) { 
        showLocationPermissionModal.value = true;
        return; 
    }
    
    intentAfterLocation.value = 'grid';

    // Si ya tenemos coordenadas en el perfil del usuario, vamos directo al GPS sin modal previo
    if (props.user?.latitude && props.user?.longitude) {
        confirmarUbicacion();
    } else {
        showLocationPermissionModal.value = true;
    }
};

const toggleMapa = () => {
    if (viewMode.value === 'grid') {
        // Si no hay coordenadas ni en el form ni en el perfil, pedimos permiso
        if (!form.lat && !form.lon && (!props.user?.latitude || !props.user?.longitude)) {
            intentAfterLocation.value = 'map';
            showLocationPermissionModal.value = true;
        } else {
            // Si ya tenemos algo, intentamos ir al mapa directamente
            viewMode.value = 'map';
        }
    } else {
        viewMode.value = 'grid';
    }
};

const confirmarUbicacion = () => {
    showLocationPermissionModal.value = false;
    isLoading.value = true;
    
    navigator.geolocation.getCurrentPosition((position) => {
        form.lat = position.coords.latitude;
        form.lon = position.coords.longitude;
        form.filtro_rapido = '';
        viewMode.value = intentAfterLocation.value;
        submit();
    }, () => {
        isLoading.value = false;
        // Si falla el GPS y no tenemos NADA previo (ni form ni perfil), bloqueamos
        if (!form.lat && !form.lon && (!props.user?.latitude || !props.user?.longitude)) {
            error('No se pudo obtener tu ubicación. Esta función requiere GPS.');
        } else {
            // Si teníamos algo previo (ej: perfil), cargamos eso si queríamos ir al mapa
            if (intentAfterLocation.value === 'map') {
                viewMode.value = 'map';
            }
            error('No pudimos actualizar tu ubicación exacta por GPS.');
        }
    });
};

const limpiarFiltros = () => {
    form.reset();
    form.interests = []; form.availability_general = [];
    isLoading.value = true;
    submit();
};

const handleCitySelected = (cityData) => {
    form.city = cityData.name;
    form.lat = cityData.lat;
    form.lon = cityData.lon;
};
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
                            🔥 Más activos
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
                    <div class="pt-6 border-t border-gray-100 dark:border-gray-700 animate-fade-in-down space-y-6 transition-colors" v-show="showFilters">
                        
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
                                <CityAutocomplete 
                                    v-model="form.city" 
                                    :api-key="geoapify_key" 
                                    @city-selected="handleCitySelected" 
                                    class="mt-1"
                                />
                            </div>
                        </div>

                         <!-- Fila 1.5: Gimnasio (Nuevo) -->
                         <div v-if="gymsInCityOptions.length > 0">
                                <SelectInput
                                    id="gym_id"
                                    label="🏋️ Filtrar por Gimnasio"
                                    v-model="form.gym_id"
                                    :options="gymsInCityOptions"
                                />
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
                        <label class="block text-sm font-bold text-indigo-700 dark:text-indigo-400 mb-3 flex items-center gap-2">
                            <span class="text-lg">🤝</span>
                            <span>Buscar compañero para</span>
                        </label>
                        <SelectInput
                            id="interest_id"
                            v-model="form.interest_id"
                            :options="interestOptions"
                        />
                        <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-2 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                            Las tarjetas que coincidan se resaltarán automáticamente
                        </p>
                    </div>
                </div>

                <!-- Botón Alternar Mapa/Lista (Ubicado debajo de filtros) -->
                <div v-if="!form.filtro_rapido && !form.lat" class="flex justify-end mb-6">
                    <button 
                        @click="toggleMapa"
                        class="flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold shadow-sm border transition-all duration-200"
                        :class="viewMode === 'grid' 
                            ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 hover:shadow-md' 
                            : 'bg-indigo-600 text-white border-transparent hover:bg-indigo-700 shadow-md'"
                    >
                        <span v-if="viewMode === 'grid'" class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                            Ver Mapa
                        </span>
                        <span v-else class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                            Ver Lista
                        </span>
                    </button>
                </div>

                <!-- VISTA MAPA (Inmersiva) -->
                <div v-if="viewMode === 'map'" class="relative h-[500px] md:h-[650px] w-full rounded-2xl overflow-hidden shadow-2xl border border-gray-200 dark:border-gray-700 mb-8 z-0 transition-all duration-500">
                    
                    <!-- Controles Flotantes (Glassmorphism) -->
                    <div class="absolute top-4 right-4 z-[400] flex flex-col gap-2">
                        <button @click="viewMode = 'grid'" class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-3 rounded-full shadow-lg hover:scale-110 transition-transform text-gray-700 dark:text-gray-200" title="Volver a lista">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                        </button>
                        <button v-if="form.lat" @click="$refs.map.leafletObject.flyTo([form.lat, form.lon], 14)" class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-3 rounded-full shadow-lg hover:scale-110 transition-transform text-indigo-600 dark:text-indigo-400" title="Mi ubicación">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </button>
                    </div>

                    <l-map 
                        ref="map" 
                        v-model:zoom="zoom" 
                        :center="mapCenter" 
                        :use-global-leaflet="false" 
                        :options="{zoomControl: false}"
                        @ready="onMapReady"
                        @moveend="onMapMoveEnd"
                    >
                        <l-tile-layer
                            url="https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png"
                            layer-type="base"
                            name="CartoDB Voyager"
                        ></l-tile-layer>

                        <!-- Radio de Búsqueda (Radar) -->
                        <l-circle 
                            v-if="form.lat && form.lon"
                            :lat-lng="[form.lat, form.lon]"
                            :radius="3000"
                            color="#6366f1"
                            :weight="1"
                            fill-color="#6366f1"
                            :fill-opacity="0.08"
                            class-name="radar-animation"
                        />

                        <!-- Marcador Usuario Actual (Tú) -->
                        <l-marker v-if="form.lat && form.lon" :lat-lng="[form.lat, form.lon]">
                             <l-icon class-name="custom-me-icon" :icon-anchor="[10, 10]">
                                 <div class="w-5 h-5 bg-indigo-600 rounded-full border-2 border-white shadow-xl pulse-ring relative z-50"></div>
                             </l-icon>
                            <l-popup :options="{ closeButton: false, offset: [0, -5], className: 'me-popup' }">
                                <div class="text-center font-bold text-xs px-2 py-1">📍 Tú estás aquí</div>
                            </l-popup>
                        </l-marker>

                        <!-- AQUI IRÁN LOS GIMNASIOS (OSM) -->
                        <l-marker 
                            v-for="gym in gyms" 
                            :key="'gym-'+gym.id" 
                            :lat-lng="[parseFloat(gym.lat), parseFloat(gym.lon)]"
                        >
                            <l-icon class-name="custom-gym-icon" :icon-anchor="[16, 16]">
                                <div class="w-8 h-8 flex items-center justify-center bg-gray-900 dark:bg-black border-2 rounded-lg shadow-xl text-lg hover:scale-110 transition-transform cursor-pointer" :class="gym.colorClass || 'border-indigo-500'">
                                    {{ gym.icon || '🏋️' }}
                                </div>
                            </l-icon>
                            
                            <l-popup :options="{ closeButton: false, offset: [0, -10], className: 'premium-popup' }">
                                <div class="text-center p-1 w-48 font-sans">
                                    <h4 class="font-bold text-gray-900 text-sm leading-tight mb-0.5">{{ gym.name }}</h4>
                                    <p class="text-[10px] text-gray-500 font-bold tracking-wide mb-2">{{ gym.type }}</p>
                                    
                                    <div v-if="gym.address" class="text-[10px] text-gray-500 mb-1 flex items-start justify-center gap-1">
                                        <span>📍</span> <span class="text-left line-clamp-2 leading-tight">{{ gym.address }}</span>
                                    </div>
                                    <a v-if="gym.website" :href="gym.website" target="_blank" class="block text-[10px] text-indigo-500 mb-3 truncate hover:underline">🌐 Sitio Web Oficial</a>

                                    <div class="flex items-center justify-center gap-1 mb-3 bg-indigo-50 rounded-lg py-1">
                                        <div class="flex -space-x-1">
                                            <div class="w-3 h-3 rounded-full bg-indigo-200 border border-white"></div>
                                            <div class="w-3 h-3 rounded-full bg-indigo-300 border border-white"></div>
                                        </div>
                                        <p class="text-[10px] text-indigo-700 font-bold"> {{ gym.users_count }} GymPals</p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <button 
                                            @click="openGymModal(gym.id)"
                                            class="bg-white border border-gray-200 text-gray-700 text-[10px] font-bold py-2 rounded-lg hover:bg-gray-50 transition-colors"
                                        >
                                            Ver GymPals
                                        </button>
                                        <button 
                                            @click="openGymModal(gym.id)"
                                            class="bg-indigo-600 border border-transparent text-white text-[10px] font-bold py-2 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm active:scale-95 transform"
                                        >
                                            ¡Es mi Gym!
                                        </button>
                                    </div>
                                </div>
                            </l-popup>
                        </l-marker>
                    </l-map>
                </div>

                <!-- Grid de Resultados -->
                <div v-show="viewMode === 'grid'">
                    <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <UserCardSkeleton v-for="n in 6" :key="n" />
                    </div>

                    <div v-else>
                        <div v-if="allUsers.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="user in allUsers" :key="user.id" 
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
                                 :class="getAffinityColorClass(user.affinity_score).replace('border-', 'from-').replace('ring-', '').split(' ')[0] + ' from-green-400 to-emerald-600'" 
                            >
                                {{ user.affinity_score }}% Afinidad
                            </div>

                            <!-- Header / Avatar -->
                            <div class="p-6 flex flex-col items-center flex-grow">
                                
                                <div class="relative mb-4 flex-shrink-0 w-24 h-24">
                                    <img 
                                        :src="user.profile_picture_url ? '/storage/' + user.profile_picture_url : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&background=random&color=fff'" 
                                        :alt="user.name" 
                                        class="w-full h-full rounded-full object-cover border-4 border-white dark:border-gray-700 shadow-md group-hover:scale-105 transition-transform duration-300"
                                    >
                                </div>
                                
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white text-center mt-2 transition-colors">{{ user.name }}</h3>
                                <div class="flex items-center justify-center gap-1.5 mb-3">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium transition-colors">@{{ user.username }}</p>
                                    <span v-if="user.is_following_me" class="px-1.5 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-900/40 text-[8px] font-black text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800 transition-colors uppercase tracking-widest shrink-0">
                                        Te sigue
                                    </span>
                                </div>                                
                                <!-- Ubicación y Distancia -->
                                <div class="flex items-center justify-center gap-2 mb-4 text-xs text-gray-500 dark:text-gray-400 transition-colors">
                                    <span v-if="user.location_city" class="flex items-center">
                                        📍 {{ user.location_city }}
                                    </span>
                                    <span v-if="user.distance !== undefined && user.distance !== null" class="flex items-center font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-1.5 py-0.5 rounded transition-colors">
                                        📏 {{ parseFloat(user.distance) < 0.05 ? 'Muy cerca' : 'A ' + parseFloat(user.distance).toFixed(2) + ' km' }}
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
                                              class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border shadow-sm transition-colors"
                                              :class="getInterestColorClass(interest.name)">
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

                        <!-- Trigger Infinite Scroll (Grid) -->
                        <div ref="loadMoreTrigger" class="h-20 flex items-center justify-center mt-8">
                            <div v-if="isLoadingMore" class="flex flex-col items-center gap-2">
                                <div class="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
                                <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Buscando más GymPals...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!isLoading && allUsers.length === 0" class="text-center py-16">
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
            </div>
            </div>

        <GymDetailsModal 
            :show="showGymModal" 
            :gymId="selectedGymId" 
            @close="showGymModal = false"
            @update:gym="updateGymData"
            @view-all-members="openGymMembersModal"
        />

        <GymMembersModal
            :show="showGymMembersModal"
            :gym="selectedGymForMembers"
            @close="showGymMembersModal = false"
        />

        <ConfirmModal
            :show="showLocationPermissionModal"
            type="info"
            title="📍 Permisos de Ubicación Requeridos"
            message="Para utilizar la búsqueda por cercanía y ver gimnasios en el mapa, necesitamos acceder a tu ubicación GPS."
            confirm-text="Permitir acceso"
            :show-cancel="true"
            cancel-text="Ahora no"
            @confirm="confirmarUbicacion"
            @cancel="showLocationPermissionModal = false"
        />
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

.pulse-ring {
    animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
}
@keyframes pulse-ring {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.7); }
    70% { transform: scale(3); box-shadow: 0 0 0 10px rgba(79, 70, 229, 0); }
    100% { transform: scale(3); box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
}

:deep(.radar-animation) {
    animation: radar-blink 3s infinite alternate;
}
@keyframes radar-blink {
    from { fill-opacity: 0.05; stroke-opacity: 0.2; }
    to { fill-opacity: 0.15; stroke-opacity: 0.6; }
}

/* Custom Popup Styles */
:deep(.leaflet-popup-content-wrapper) {
    border-radius: 1rem;
    padding: 0;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
:deep(.leaflet-popup-content) {
    margin: 1rem;
}
:deep(.leaflet-popup-tip) {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}
</style>