<script setup>
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import axios from 'axios';
import debounce from 'lodash.debounce';

// Componentes
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UserCard from '@/Components/UserCard.vue';
import Pagination from '@/Components/Pagination.vue';
import TagsInput from '@/Components/TagsInput.vue';
import AvailabilityInput from '@/Components/AvailabilityInput.vue';
import CityAutocomplete from '@/Components/CityAutocomplete.vue';
import SelectInput from '@/Components/SelectInput.vue';

const geoapifyKey = usePage().props.geoapify_key;
const props = defineProps({
    users: Object,
    sugerencias: Array,
    filters: Object,
    title: String,
    interests: Array,
    searchedInterestId: [String, Number, null],
});

function handleCitySelected(cityData) {
    searchForm.city = cityData.name;
}

const manualFiltersVisible = ref(false);
const geolocationStatus = ref('');

const searchForm = useForm({
    search: props.filters.search || '',
    city: props.filters.city || '',
    interests: props.filters.interests || [],
    availability_general: props.filters.availability_general || [],
    experience_level: props.filters.experience_level || '',
});

const submitManualFilters = () => {
    router.get(route('discover.index'), searchForm.data(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

function searchNearby() {
    if (!navigator.geolocation) {
        geolocationStatus.value = "Tu navegador no soporta geolocalización.";
        return;
    }
    geolocationStatus.value = "Solicitando permiso...";

    const success = (position) => {
        geolocationStatus.value = "¡Ubicación encontrada! Buscando...";
        router.get(route('discover.index'), {
            lat: position.coords.latitude,
            lon: position.coords.longitude
        }, {
            replace: true,
            onFinish: () => { geolocationStatus.value = ''; }
        });
    };

    const error = (err) => {
        geolocationStatus.value = 'No se pudo obtener la ubicación.';
        console.error(`ERROR DE GEOLOCALIZACIÓN (${err.code}): ${err.message}`);
    };

    navigator.geolocation.getCurrentPosition(success, error, { timeout: 10000 });
}

// --- LÓGICA PARA "BUSCANDO COMPAÑERO" ---
const defaultUserInterestId = computed(() => usePage().props.auth.user?.looking_for_interest_id);
// Usamos props.searchedInterestId directamente, ya que lo estamos pasando desde el controlador
const selectedInterestId = ref(props.searchedInterestId);

// Watch #1: Se encarga de SINCRONIZAR el 'ref' con los props de la página
watch(() => props.searchedInterestId, (newFilterId) => {
    selectedInterestId.value = newFilterId || defaultUserInterestId.value || null;
}, { immediate: true });

const performSearch = (interestId) => {
    saveInterestPreference(interestId);

    router.get(route('discover.index'), {
        filtro_rapido: 'buscando_companero',
        interest_id: interestId
    }, {
        replace: true,
        preserveScroll: true,
        preserveState: true,
    });
};

const debouncedSearch = debounce(performSearch, 500);

watch(selectedInterestId, (newId, oldId) => {
    if (oldId !== undefined && newId !== oldId) {
        debouncedSearch(newId);
    }
});


// La función 'saveInterestPreference' se mantiene exactamente igual.
function saveInterestPreference(interestId) {
    if (!interestId) return;
    axios.patch(route('profile.updateLookingFor'), {
        interest_id: interestId
    }).catch(error => {
        console.error("Error al guardar la preferencia de interés:", error);
    });
}

const filtrosActivos = computed(() => usePage().props.filters);
const quickFilterActive = computed(() => !!filtrosActivos.value.filtro_rapido);
const isGeolocationSearch = computed(() => !!filtrosActivos.value.lat);

function clearAllFilters() {
    router.get(route('discover.index'), {}, { replace: true });
}

function applyQuickFilter(nombreFiltro) {
    if (nombreFiltro === 'buscando_companero') {
        const interestToSearch = defaultUserInterestId.value || null;
        if (!interestToSearch) {
            alert('¡Por favor, selecciona un deporte en tu perfil para usar este filtro!');
            return;
        }
        router.get(route('discover.index'), {
            filtro_rapido: nombreFiltro,
            interest_id: interestToSearch
        }, {
            replace: true,
        });
        return;
    }
    const filtroActual = filtrosActivos.value.filtro_rapido;
    const nuevoFiltro = filtroActual === nombreFiltro ? null : nombreFiltro;
    router.get(route('discover.index'), { filtro_rapido: nuevoFiltro }, { replace: true });
}

function toggleManualFilters() {
    if (!quickFilterActive.value && !isGeolocationSearch.value) {
        manualFiltersVisible.value = !manualFiltersVisible.value;
    }
}
</script>

<template>
    <Head :title="title || 'Conectar'" />

    <AuthenticatedLayout>
        <div class="flex flex-col items-center justify-center pt-8 mb-8">
            <h2 class="text-3xl font-extrabold text-indigo-700 tracking-tight">Conectar</h2>
            <p class="text-lg text-gray-600 max-w-xl text-center">Encuentra compañeros para entrenar y haz nuevos amigos.</p>
        </div>

        <div class="container mx-auto px-2 sm:px-4 pb-8">
            
            <div v-if="sugerencias && sugerencias.length > 0" class="mb-10">
                </div>

            <div class="bg-white p-4 rounded-lg shadow-md mb-6">
                <div class="flex flex-col sm:flex-row gap-4 items-center pb-4">
                    <button @click="searchNearby" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white font-bold rounded-md shadow-lg hover:bg-indigo-700 transition transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Buscar Cerca de Mí
                    </button>
                    <p v-if="geolocationStatus" class="text-sm text-gray-600">{{ geolocationStatus }}</p>
                    <button v-if="quickFilterActive || isGeolocationSearch" @click="clearAllFilters" class="ml-auto text-sm text-indigo-600 hover:underline">Mostrar todos</button>
                </div>

                <div class="border-t border-gray-200">
                    <div class="flex flex-wrap items-center gap-4 pt-4">
                        <button @click="applyQuickFilter('mas_activos')" :class="['px-3 py-1.5 text-sm font-semibold rounded-full border-2 transition', filtrosActivos.filtro_rapido === 'mas_activos' ? 'bg-indigo-600 text-white border-indigo-700 shadow-md' : 'bg-white text-gray-800 border-gray-200 hover:bg-gray-100']">🔥 Más Activos</button>
                        <button @click="applyQuickFilter('nuevos_en_ciudad')" :class="['px-3 py-1.5 text-sm font-semibold rounded-full border-2 transition', filtrosActivos.filtro_rapido === 'nuevos_en_ciudad' ? 'bg-indigo-600 text-white border-indigo-700 shadow-md' : 'bg-white text-gray-800 border-gray-200 hover:bg-gray-100']">🏙️ Nuevos en tu ciudad</button>
                        <button @click="applyQuickFilter('buscando_companero')" :class="['px-3 py-1.5 text-sm font-semibold rounded-full border-2 transition', filtrosActivos.filtro_rapido === 'buscando_companero' ? 'bg-indigo-600 text-white border-indigo-700 shadow-md' : 'bg-white text-gray-800 border-gray-200 hover:bg-gray-100']">🤝 Buscando Compañero</button>
                    </div>
                    
                    <div class="border-t border-gray-200 mt-4 pt-4 flex items-center justify-between">
                        <button @click="toggleManualFilters" :disabled="quickFilterActive || isGeolocationSearch" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-2 disabled:text-gray-400 disabled:cursor-not-allowed">
                            <svg v-if="!manualFiltersVisible" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"></path></svg>
                            Búsqueda Manual Avanzada
                        </button>
                        <button v-if="manualFiltersVisible" @click="searchForm.reset()" class="text-xs text-gray-500 hover:text-red-600 hover:underline">Limpiar filtros</button>
                    </div>

                    <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                        <div v-show="manualFiltersVisible" class="mt-4 pt-4 border-t border-gray-200">
                            <form @submit.prevent="submitManualFilters">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8">
                                    <div>
                                        <label for="search_term" class="block text-sm font-medium leading-6 text-gray-900">Buscar por nombre</label>
                                        <input type="text" v-model="searchForm.search" id="search_term" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Ej: Juan Perez">
                                    </div>
                                    <div>
                                        <label for="city_filter" class="block text-sm font-medium leading-6 text-gray-900">Ciudad</label>
                                        <CityAutocomplete v-model="searchForm.city" :api-key="geoapifyKey" @city-selected="handleCitySelected" class="mt-2"/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium leading-6 text-gray-900">Nivel</label>
                                        <SelectInput class="mt-2" v-model="searchForm.experience_level" :options="[{ value: '', label: 'Cualquiera' }, { value: 'Principiante', label: 'Principiante' }, { value: 'Intermedio', label: 'Intermedio' }, { value: 'Avanzado', label: 'Avanzado' }]" />
                                    </div>
                                    <div class="col-span-1 md:col-span-full">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Disponibilidad</label>
                                        <AvailabilityInput v-model="searchForm.availability_general" />
                                    </div>
                                    <div class="col-span-1 md:col-span-full">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Intereses deportivos</label>
                                        <TagsInput v-model="searchForm.interests" :interests="interests" />
                                    </div>
                                </div>
                                <div class="mt-6 flex justify-end">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-bold rounded-md shadow-sm hover:bg-indigo-700">
                                        Aplicar Filtros
                                    </button>
                                </div>
                            </form>
                        </div>
                    </transition>
                </div>
            </div>

            <div v-if="filtrosActivos.filtro_rapido === 'buscando_companero'" 
                class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded-md mb-6 shadow-sm">
                <div class="flex flex-col items-center gap-y-2">
                    <label for="interest-select" class="block text-sm font-semibold text-indigo-800">Buscando compañeros para:</label>
                    <select id="interest-select" v-model="selectedInterestId"
                            class="block w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option v-if="!selectedInterestId" :value="null" disabled>Selecciona un deporte</option>
                        <option v-for="interest in interests" :key="interest.id" :value="interest.id">
                            {{ interest.name }}
                        </option>
                    </select>
                </div>
            </div>


            <div v-if="users.data && users.data.length > 0">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <UserCard v-for="user in users.data" :key="user.id" :user="user"  :active-filter="filtrosActivos.filtro_rapido" :searched-interest-id="props.searchedInterestId" />
                </div>
                <Pagination :links="users.links" class="mt-6" />
            </div>
            <div v-else class="text-center py-10 bg-white rounded-lg shadow-md">
                <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron GymPals</h3>
                <p class="mt-1 text-sm text-gray-500">Intenta ajustar tus filtros de búsqueda o usa un filtro rápido.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>