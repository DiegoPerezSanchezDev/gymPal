<script setup>
import { Link } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    activeFilter: {
        type: String,
        default: ''
    },
    searchedInterestId: {
        type: [Number, String, null],
        default: null
    }
});

const fitnessInterests = computed(() => Array.isArray(props.user.fitnessInterests) ? props.user.fitnessInterests : []);
const commonInterestIds = computed(() => Array.isArray(props.user.common_interests_ids) ? props.user.common_interests_ids : []);

const isMatchingInterestSearch = computed(() => {
    return props.activeFilter === 'buscando_companero' &&
        props.user.looking_for_interest &&
        String(props.user.looking_for_interest.id) === String(props.searchedInterestId);
});

// Añadimos un console.log para depurar la data
onMounted(() => {
    console.log('Active Filter:', props.activeFilter);
    console.log('Todos:', props.user.fitnessInterests );

});
</script>

<template>
    <div 
        class="bg-white shadow-md rounded-lg p-4 flex flex-col items-center text-center hover:shadow-lg transition-shadow"
        :class="{ 'border-2 border-emerald-500 shadow-xl': isMatchingInterestSearch }"
    >
        <Link :href="route('profile.show.public', { user: user.username })">
            <img :src="user.profile_picture_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&background=random&color=fff'"
                :alt="user.name"
                class="w-24 h-24 rounded-full mb-3 object-cover border-2 border-gray-200">
        </Link>
        <Link :href="route('profile.show.public', { user: user.username })" class="block">
            <h3 class="text-lg font-semibold text-gray-800 hover:text-indigo-600">{{ user.display_name || user.name }}</h3>
        </Link>
        <p v-if="user.username" class="text-sm text-gray-500">@{{ user.username }}</p>
        <p v-if="user.location_city" class="text-xs text-gray-500 mt-1">{{ user.location_city }}</p>

        <!-- Muestra la distancia si ha sido calculada por el backend en los filtros relevantes -->
        <p v-if="user.distance !== undefined && (activeFilter === 'nuevos_en_ciudad' || activeFilter === 'buscando_companero')" class="text-xs font-semibold text-indigo-600 mt-1">
            📍 A {{ Math.round(user.distance) }} km de ti
        </p>

        <!-- Score de Afinidad -->
        <div v-if="user.affinity_score !== undefined" class="w-full mt-3 mb-1">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-medium text-gray-700">Afinidad</span>
                <span :class="['text-xs font-bold', user.affinity_score >= 80 ? 'text-green-600' : user.affinity_score >= 50 ? 'text-yellow-600' : 'text-gray-400']">
                    {{ user.affinity_score }}%
                </span>
            </div>
            <div class="w-full h-2 bg-gray-200 rounded-full">
                <div :style="{ width: user.affinity_score + '%' }"
                    :class="['h-2 rounded-full transition-all', user.affinity_score >= 80 ? 'bg-green-400' : user.affinity_score >= 50 ? 'bg-yellow-400' : 'bg-gray-300']">
                </div>
            </div>
        </div>

        <!-- Contenedor de Atributos con Lógica Condicional -->
        <div class="w-full flex flex-col items-center gap-y-3 mt-2">
            <!-- Etiqueta de texto de Afinidad -->
            <div v-if="user.affinity_score !== undefined">
                <span v-if="user.affinity_score >= 100" class="inline-block px-2 py-0.5 bg-green-200 text-green-800 text-xs rounded-full font-semibold">¡Afinidad perfecta!</span>
                <span v-else-if="user.affinity_score >= 80" class="inline-block px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full font-semibold">¡Alta afinidad!</span>
                <span v-else-if="user.affinity_score >= 50" class="inline-block px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs rounded-full font-semibold">Afinidad media</span>
                <span v-else class="inline-block px-2 py-0.5 bg-gray-100 text-gray-500 text-xs rounded-full font-semibold">Baja afinidad</span>
            </div>

            <!-- ========= LÓGICA DE VISUALIZACIÓN CLAVE ========= -->

            <!-- 1. Se muestra SÓLO si el filtro es 'buscando_companero' -->
            <div v-if="activeFilter === 'buscando_companero' && user.looking_for_interest" class="w-full text-center">
                <p class="text-xs text-gray-500 mb-1">Busca compañero para:</p>
                <span
                    class="inline-block px-3 py-1 text-sm font-bold rounded-full transition-all"
                    :class="[ isMatchingInterestSearch ? 'bg-emerald-500 text-white shadow-lg' : 'bg-teal-100 text-teal-800' ]">
                    {{ user.looking_for_interest.name }}
                </span>
            </div>

            <!-- 2. Se muestra SÓLO si NO HAY FILTRO RÁPIDO ACTIVO (la vista principal) -->
            <div v-else-if="!activeFilter && fitnessInterests.length > 0" class="flex flex-wrap gap-1 justify-center">
                <span v-for="interest in fitnessInterests"
                    :key="interest.id"
                    :class="[
                        'px-2 py-0.5 text-xs font-medium rounded-full',
                        commonInterestIds.includes(interest.id) 
                            ? 'bg-indigo-600 text-white border border-indigo-800' 
                            : 'bg-indigo-50 text-indigo-800 border border-indigo-200'
                    ]">
                    {{ interest.name }}
                </span>
            </div>

            <!-- La disponibilidad se muestra en todas las vistas EXCEPTO en 'buscando_companero' -->
            <div v-if="activeFilter !== 'buscando_companero' && user.availability_general && user.availability_general.length > 0" class="flex flex-wrap gap-1 justify-center">
                <span v-for="slot in user.availability_general"
                    :key="slot"
                    :class="['px-2 py-0.5 text-xs font-medium rounded-full', (user.common_availability || []).includes(slot) ? 'bg-pink-600 text-white border border-pink-800' : 'bg-pink-100 text-pink-700 border border-pink-200']">
                    {{ slot }}
                </span>
            </div>
        </div>

        <!-- Botones inferiores -->
        <div class="mt-4 flex gap-2 w-full justify-center">
            <Link :href="route('profile.show.public', { user: user.username })"
                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                Ver Perfil
            </Link>
            <button type="button" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-green-400 to-pink-500">
                Conectar
            </button>
        </div>
    </div>
</template>
