// resources/js/Pages/Profile/ShowPublic.vue
<script setup>
// Importaciones y props
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// Props que recibe el perfil público
const props = defineProps({
    profileUser: Object, // Datos del usuario a mostrar
    posts: Object,       // Publicaciones (no se muestran en este diseño)
    title: String,
    isFollowing: Boolean,
    isOwnProfile: Boolean,
});

// Estado local para seguir/dejar de seguir
const localIsFollowing = ref(props.isFollowing);
const processingFollow = ref(false);

// Acción: seguir/dejar de seguir
const followUser = () => {
    if (props.isOwnProfile || processingFollow.value) return;
    processingFollow.value = true;
    router.post(route('profile.toggleFollow', props.profileUser.id), {}, {
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props.isFollowing !== undefined) {
                localIsFollowing.value = page.props.isFollowing;
            } else {
            router.reload({ only: ['isFollowing', 'profileUser'], preserveScroll: true });
            }
        },
        onError: (errors) => {
            console.error('Error al seguir/dejar de seguir:', errors);
        },
        onFinish: () => {
            processingFollow.value = false;
        }
    });
};

// Acción: enviar mensaje
const sendMessage = () => {
    if (props.isOwnProfile) return;
    router.visit(route('chat.show', { user: props.profileUser.username }));
};

// Avatar del usuario (placeholder si no tiene foto)
const avatarUrl = computed(() => {
    return props.profileUser.profile_picture_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(props.profileUser.name || 'G P')}&background=random&color=fff&size=160`;
});

// Intereses deportivos como array
const fitnessInterestsArray = computed(() => {
    if (!props.profileUser || !props.profileUser.fitness_interests) {
        return [];
    }
    if (Array.isArray(props.profileUser.fitness_interests)) {
        return props.profileUser.fitness_interests;
    }
    if (typeof props.profileUser.fitness_interests === 'string') {
        return props.profileUser.fitness_interests.split(',').map(i => i.trim()).filter(i => i);
    }
    return [];
});
// Propiedad computada para estilos dinámicos del nivel de experiencia
const levelStyles = computed(() => {
    const level = props.profileUser.experience_level;
    // Hacemos el switch insensible a mayúsculas/minúsculas
    switch (level?.toLowerCase()) {
        case 'principiante':
            return {
                span: 'bg-fuchsia-100 text-fuchsia-800',
                svg: 'text-fuchsia-500'
            };
        case 'intermedio':
            return {
                span: 'bg-teal-100 text-teal-800', // O green
                svg: 'text-teal-500'
            };
        case 'avanzado':
            return {
                span: 'bg-amber-100 text-amber-800', // O purple
                svg: 'text-amber-500'
            };
        default:
            return {
                span: 'bg-blue-100 text-blue-800',
                svg: 'text-gray-500'
            };
    }
});

</script>

<template>
    <Head :title="title || 'Perfil de Usuario'" />
    <AuthenticatedLayout>
        <div v-if="profileUser" class="container mx-auto px-2 sm:px-4 py-8">
            <!-- Tarjeta principal del perfil -->
            <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-8 flex flex-col items-center">
                <!-- Avatar y nombre -->
                <img :src="avatarUrl" :alt="profileUser.name" class="w-32 h-32 rounded-full object-cover border-4 border-indigo-200 shadow mb-4">
                <h1 class="text-3xl font-bold text-gray-800 mb-1">{{ profileUser.display_name || profileUser.name }}</h1>
                <p v-if="profileUser.username" class="text-md text-indigo-600 mb-2">@{{ profileUser.username }}</p>
                <!-- Bio -->
                <p v-if="profileUser.bio" class="text-gray-600 text-center mb-4 whitespace-pre-line">{{ profileUser.bio }}</p>
                <!-- Ciudad y disponibilidad en una línea -->
                <div class="flex flex-col items-center gap-y-4 w-full mt-2">
                <div class="flex flex-wrap gap-2 justify-center mb-4 items-center">
                    <span v-if="profileUser.location_city" class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">
                        <svg class="w-4 h-4 mr-1 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m13-6.13a4 4 0 11-8 0 4 4 0 018 0zM5 8a4 4 0 108 0 4 4 0 00-8 0z" /></svg>
                        {{ profileUser.location_city }}
                    </span>
                    <template v-if="profileUser.availability_general && profileUser.availability_general.length">
                        <span v-for="slot in profileUser.availability_general" :key="slot" class="inline-block bg-pink-100 text-pink-700 text-xs font-semibold px-3 py-1 rounded-full">
                            {{ slot }}
                        </span>
                    </template>
                </div>
                <!-- Nivel de experiencia -->
                <div v-if="profileUser.experience_level" class="mb-2 flex flex-wrap gap-2 justify-center">
                    <span 
                        class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full"
                        :class="levelStyles.span"
                    >
                        <svg 
                            class="w-4 h-4 mr-1" 
                            :class="levelStyles.svg"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        {{ profileUser.experience_level }}
                    </span>
                </div>
                <!-- Intereses deportivos -->
                <div v-if="fitnessInterestsArray.length" class="mb-4 flex flex-wrap gap-2 justify-center">
                    <span v-for="interest in fitnessInterestsArray" :key="interest.id || interest" class="inline-block bg-indigo-100 text-indigo-800 text-xs font-semibold px-3 py-1 rounded-full">
                        {{ interest.name || interest }}
                    </span>
                </div>
            </div>
                <!-- Estadísticas -->
                <div class="flex gap-6 justify-center mb-4">
                    <div class="text-center">
                        <span class="block text-xl font-bold text-gray-700">{{ profileUser.followers_count ?? 0 }}</span>
                        <span class="text-xs text-gray-500">Seguidores</span>
                    </div>
                    <div class="text-center">
                        <span class="block text-xl font-bold text-gray-700">{{ profileUser.following_count ?? 0 }}</span>
                        <span class="text-xs text-gray-500">Siguiendo</span>
                    </div>
                    <div class="text-center">
                        <span class="block text-xl font-bold text-gray-700">{{ profileUser.posts_count ?? 0 }}</span>
                        <span class="text-xs text-gray-500">Publicaciones</span>
                    </div>
                </div>
                <!-- Fecha de registro -->
                <div v-if="profileUser.created_at" class="mb-4 text-xs text-gray-400 text-center">
                    Miembro desde {{ new Date(profileUser.created_at).toLocaleDateString('es-ES', { year: 'numeric', month: 'long' }) }}
                </div>
                <!-- Botones de acción -->
                <div class="grid grid-cols-2 gap-3 mt-4 w-full max-w-xs mx-auto">
                    <!-- Si es tu propio perfil, solo muestra Editar -->
                    <Link v-if="isOwnProfile" :href="route('profile.edit')"
                        class="col-span-2 inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                        Editar Perfil
                    </Link>
                    <!-- Si es otro usuario, muestra todas las acciones -->
                    <template v-else>
                        <!-- Conectar (puedes personalizar la acción) -->
                        <button @click="() => navigator.clipboard.writeText(window.location.href)"
                            class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-green-400 via-indigo-400 to-pink-400 hover:from-green-500 hover:to-pink-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400 transition w-full">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Conectar
                        </button>
                        <!-- Enviar mensaje (icono chat) -->
                        <button @click="sendMessage"
                            :disabled="processingFollow"
                            class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition w-full">
                            <svg class="w-4 h-4 mr-1 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" /></svg>
                            Mensaje
                        </button>
                        <!-- Compartir perfil (icono avión de papel) -->
                        <button @click="() => navigator.clipboard.writeText(window.location.href)"
                            class="col-span-2 inline-flex items-center justify-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400 transition w-full">
                            <svg class="w-4 h-4 mr-1 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10l9-6 9 6-9 12-9-12z" /></svg>
                            Compartir
                        </button>
                        <!-- Reportar usuario -->
                        <button @click="() => alert('Funcionalidad de reportar próximamente')"
                            class="col-span-2 inline-flex items-center justify-center px-4 py-2 bg-red-100 border border-red-300 rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-400 transition w-full">
                            <svg class="w-4 h-4 mr-1 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-1.414 1.414M6.343 17.657l-1.414 1.414M12 3v2m0 14v2m9-9h-2M5 12H3m15.364-6.364l-1.414 1.414M6.343 6.343L4.929 4.929" /></svg>
                            Reportar Usuario
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>