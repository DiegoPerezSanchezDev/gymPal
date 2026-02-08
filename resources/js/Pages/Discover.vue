<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UserCardSkeleton from '@/Components/Skeletons/UserCardSkeleton.vue';
import GymDetailsModal from '@/Components/GymDetailsModal.vue';
import GymMembersModal from '@/Components/GymMembersModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, watch, onMounted, computed } from 'vue';
import DiscoverFilters from '@/Components/DiscoverFilters.vue';
import DiscoverMap from '@/Components/DiscoverMap.vue';
import _ from 'lodash';
import axios from 'axios';

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
    // Si la página es 1, es una búsqueda nueva o reset de filtros
    if (newUsers.current_page === 1) {
        allUsers.value = newUsers.data;
    }
    // No añadimos aquí para el resto de páginas porque loadMoreUsers ya lo hace en onSuccess
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
                
                <DiscoverFilters 
                    :form="form"
                    :interests="interests"
                    :gymsInCity="gymsInCity"
                    :geoapify_key="geoapify_key"
                    :user="user"
                    v-model:showFilters="showFilters"
                    @aplicarFiltroRapido="aplicarFiltroRapido"
                    @buscarPorUbicacion="buscarPorUbicacion"
                    @limpiarFiltros="limpiarFiltros"
                    @handleCitySelected="handleCitySelected"
                />

                <!-- Botón Alternar Mapa/Lista -->
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

                <!-- VISTA MAPA -->
                <DiscoverMap 
                    v-if="viewMode === 'map'"
                    v-model:zoom="zoom"
                    :gyms="gyms"
                    :mapCenter="mapCenter"
                    :formLat="form.lat"
                    :formLon="form.lon"
                    @map-ready="onMapReady"
                    @map-moveend="onMapMoveEnd"
                    @open-gym-modal="openGymModal"
                    @open-gym-members-modal="openGymMembersModal"
                />

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