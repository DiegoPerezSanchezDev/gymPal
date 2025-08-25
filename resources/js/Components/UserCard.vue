<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineProps({
    user: {
        type: Object,
        required: true,
        default: () => ({
            id: null,
            name: 'GymPal User',
            username: 'gympaluser',
            profile_picture_url: 'https://ui-avatars.com/api/?name=G+P&background=random&color=fff',
            location_city: 'Ciudad Desconocida',
            fitnessInterests: [],
            availability_general: [],
            affinity_score: 0,
            common_interests: [],
            common_availability: [],
        })
    }
});

const page = usePage();
const allInterests = computed(() => page.props.interests || []);
function getInterestName(id) {
    const found = allInterests.value.find(i => i.id === id);
    return found ? found.name : 'Interés';
}
</script>

<template>
    <div class="bg-white shadow-md rounded-lg p-4 flex flex-col items-center text-center hover:shadow-lg transition-shadow">
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

        <p v-if="user.distance !== undefined" class="text-xs font-semibold text-indigo-600 mt-1">
            📍 A {{ Math.round(user.distance) }} km de ti
        </p>

        <!-- Score de afinidad visual (solo la barra) -->
        <div v-if="user.affinity_score !== undefined" class="w-full mt-3 mb-1">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-medium text-gray-700">Afinidad</span>
                <span :class="[
                    'text-xs font-bold',
                    user.affinity_score >= 80 ? 'text-green-600' : user.affinity_score >= 50 ? 'text-yellow-600' : 'text-gray-400'
                ]">
                    {{ user.affinity_score }}%
                </span>
            </div>
            <div class="w-full h-2 bg-gray-200 rounded-full">
                <div :style="{ width: user.affinity_score + '%' }"
                    :class="[
                        'h-2 rounded-full transition-all',
                        user.affinity_score >= 80 ? 'bg-green-400' : user.affinity_score >= 50 ? 'bg-yellow-400' : 'bg-gray-300'
                    ]">
                </div>
            </div>
        </div>

        <!-- Contenedor de Atributos con espaciado vertical -->
        <div class="w-full flex flex-col items-center gap-y-3 mt-2">

            <!-- Etiqueta de Texto de Afinidad -->
            <div v-if="user.affinity_score !== undefined">
                <span v-if="user.affinity_score === 100" class="inline-block px-2 py-0.5 bg-green-200 text-green-800 text-xs rounded-full font-semibold">¡Afinidad perfecta!</span>
                <span v-else-if="user.affinity_score >= 80" class="inline-block px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full font-semibold">¡Alta afinidad!</span>
                <span v-else-if="user.affinity_score >= 50" class="inline-block px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs rounded-full font-semibold">Afinidad media</span>
                <span v-else class="inline-block px-2 py-0.5 bg-gray-100 text-gray-500 text-xs rounded-full font-semibold">Baja afinidad</span>
            </div>

            <!-- Actividades que realiza el usuario -->
            <div v-if="user.fitnessInterests && user.fitnessInterests.length > 0" class="flex flex-wrap gap-1 justify-center">
                <span
                    v-for="interest in user.fitnessInterests"
                    :key="interest.id"
                    :class="[
                        'px-2 py-0.5 text-xs font-medium rounded-full',
                        (user.common_interests || []).includes(interest.id)
                            ? 'bg-indigo-600 text-white border border-indigo-800' // más fuerte si es común
                            : 'bg-indigo-50 text-indigo-800 border border-indigo-200' // normal si no
                    ]"
                >
                    {{ interest.name }}
                </span>
            </div>

            <!-- Disponibilidad del usuario -->
            <div v-if="user.availability_general && user.availability_general.length > 0" class="flex flex-wrap gap-1 justify-center">
                <span
                    v-for="slot in user.availability_general"
                    :key="slot"
                    :class="[
                        'px-2 py-0.5 text-xs font-medium rounded-full',
                        (user.common_availability || []).includes(slot)
                            ? 'bg-pink-600 text-white border border-pink-800' // más fuerte si es común
                            : 'bg-pink-100 text-pink-700 border border-pink-200' // normal si no
                    ]"
                >
                    {{ slot }}
                </span>
            </div>
            
        </div>

        <div class="mt-4 flex gap-2 w-full justify-center">
            <Link :href="route('profile.show.public', { user: user.username })"
                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Ver Perfil
            </Link>
            <button type="button"
                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-green-400 via-indigo-400 to-pink-400 hover:from-green-500 hover:to-pink-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Conectar
            </button>
        </div>
    </div>
</template>