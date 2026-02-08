<script setup>
import { computed } from 'vue';
import SelectInput from '@/Components/SelectInput.vue';
import MultiSelectInput from '@/Components/MultiSelectInput.vue';
import CityAutocomplete from '@/Components/CityAutocomplete.vue';

const props = defineProps({
    form: Object,
    interests: Array,
    gymsInCity: Array,
    geoapify_key: String,
    user: Object,
    showFilters: Boolean
});

const emit = defineEmits(['update:showFilters', 'aplicarFiltroRapido', 'buscarPorUbicacion', 'limpiarFiltros', 'handleCitySelected']);

// Options constants
const experienceOptions = [
    { value: '', label: '✨ Todos los niveles' },
    { value: 'Principiante', label: '🌱 Principiante' },
    { value: 'Intermedio', label: '⚡ Intermedio' },
    { value: 'Avanzado', label: '🔥 Avanzado' }
];

const availabilityOptions = [
    { value: 'Mañana', label: 'Mañana' },
    { value: 'Tarde', label: 'Tarde' },
    { value: 'Noche', label: 'Noche' },
    { value: 'Finde semana', label: 'Finde semana' }
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

</script>

<template>
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
                @click="emit('aplicarFiltroRapido', 'mas_activos')"
                class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border"
                :class="form.filtro_rapido === 'mas_activos' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-700 shadow-sm' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'"
            >
                🔥 Más activos
            </button>
            <button 
                @click="emit('aplicarFiltroRapido', 'nuevos_en_ciudad')"
                class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border"
                :class="form.filtro_rapido === 'nuevos_en_ciudad' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-700 shadow-sm' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'"
            >
                🏙️ Nuevos en mi ciudad
            </button>
            <button 
                @click="emit('aplicarFiltroRapido', 'buscando_companero')"
                class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border"
                :class="form.filtro_rapido === 'buscando_companero' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-700 shadow-sm' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'"
            >
                🤝 Buscar compañero
            </button>
            <button 
                @click="emit('buscarPorUbicacion')"
                class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border"
                :class="form.lat ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 border-green-200 dark:border-green-700 shadow-sm' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'"
            >
                📍 Cerca de mí
            </button>
            
            <button 
                @click="emit('update:showFilters', !showFilters)"
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
                        @city-selected="emit('handleCitySelected', $event)" 
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
                <button @click="emit('limpiarFiltros')" class="text-sm text-red-500 hover:text-red-700 font-bold flex items-center gap-1 hover:gap-2 transition-all">
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
</template>
