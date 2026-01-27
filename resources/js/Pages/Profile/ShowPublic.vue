
<script setup>
// Importaciones y props
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProfileSkeleton from '@/Components/Skeletons/ProfileSkeleton.vue';
import PostCard from '@/Components/PostCard.vue';
import PostGridModal from '@/Components/PostGridModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import Modal from '@/Components/Modal.vue';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
import ProfileStats from '@/Components/ProfileStats.vue';
import GymDetailsModal from '@/Components/GymDetailsModal.vue';
import GymMembersModal from '@/Components/GymMembersModal.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, watch } from 'vue';
import { useToast } from '@/composables/useToast';


// Props que recibe el perfil público
const props = defineProps({
    profileUser: Object,
    title: String,
    isOwnProfile: Boolean,
    connection_status: String,
    connection_id: Number,
    is_following_me: Boolean, // Indica si el perfil visitado me sigue
    are_gym_pals: Boolean, // Indica si somos GymPals (conexión mutua aceptada)
    gym_pals_count: Number,
    followers_count: Number,
    following_count: Number,
    gym_pals_list: Array,
    followers_list: Array,
    following_list: Array,
    stats: Object, // Estadísticas del usuario
    posts: Array, // Posts del usuario
    workouts: {
        type: Array,
        default: () => []
    }, // Rutinas del usuario
    progressCharts: Object, // Gráficas de progreso (V2)
});

const processingConnection = ref(false);
const isLoading = ref(true);
const showConnectionsModal = ref(false);
const connectionsModalType = ref('gymPals'); // 'gymPals', 'followers', 'following'
const activeTab = ref('posts'); // 'posts', 'workouts', 'stats'
const showPostModal = ref(false);
const selectedPostIndex = ref(0);
const showRejectModal = ref(false);
const showDisconnectModal = ref(false);
const showColorModal = ref(false); // Estado para el modal de colores
const showGymModal = ref(false);
const selectedGymId = ref(null);
const showGymMembersModal = ref(false);
const selectedGymForMembers = ref(null);

// Open Gym Modal
const openGymModal = (gymId) => {
    selectedGymId.value = gymId;
    showGymModal.value = true;
    showAllGymsModal.value = false; // Cerrar lista si estaba abierta
};

// Open Gym Members Modal
const openGymMembersModal = (gym) => {
    selectedGymForMembers.value = gym;
    showGymMembersModal.value = true;
};

const showLogoutModal = ref(false);
const confirmLogout = () => {
    router.post(route('logout'));
};

// Logic for limiting gyms (max 3)
const showAllGymsModal = ref(false);
const displayedGyms = computed(() => {
    if (!props.profileUser.gyms) return [];
    return props.profileUser.gyms.slice(0, 3);
});
const remainingGymsCount = computed(() => {
    if (!props.profileUser.gyms) return 0;
    return Math.max(0, props.profileUser.gyms.length - 3);
});

// Paleta de colores predefinidos
const bannerColors = [
    '#6366f1', // Indigo (Default)
    '#ef4444', // Red
    '#f97316', // Orange
    '#f59e0b', // Amber
    '#84cc16', // Lime
    '#10b981', // Emerald
    '#06b6d4', // Cyan
    '#3b82f6', // Blue
    '#8b5cf6', // Violet
    '#d946ef', // Fuchsia
    '#ec4899', // Pink
    '#f43f5e', // Rose
    '#1f2937', // Gray 800
    '#111827', // Gray 900
    '#000000', // Black
    '#71717a', // Zinc
    '#78350f', // Brown
    '#831843', // Dark Pink
    '#1e3a8a', // Dark Blue
    '#14532d', // Dark Green
];

// Detectar tab desde URL
const urlParams = new URLSearchParams(window.location.search);
const tabParam = urlParams.get('tab');
const activeContentTab = ref(
    tabParam === 'rutinas' ? 'rutinas' : 
    (tabParam === 'stats' ? 'estadisticas' : 'publicaciones')
);

// Lógica para mostrar rutinas limitadas
const showAllWorkouts = ref(false);
const displayedWorkouts = computed(() => {
    if (showAllWorkouts.value) {
        return props.workouts;
    }
    return props.workouts.slice(0, 4);
});

const openPostModal = (post) => {
    // Find the index in the original posts array
    const index = props.posts.findIndex(p => p.id === post.id);
    if (index !== -1) {
        selectedPostIndex.value = index;
        showPostModal.value = true;
    }
};

// Helper para obtener URL completa de foto de perfil
const getProfilePictureUrl = (user) => {
    if (!user.profile_picture_url) {
        return `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random&color=fff`;
    }
    
    // Si ya es una URL completa, devolverla tal cual
    if (user.profile_picture_url.startsWith('http')) {
        return user.profile_picture_url;
    }
    
    // Si es una ruta relativa, añadir /storage/ si no lo tiene
    let path = user.profile_picture_url.replace(/^\//, '');
    if (!path.startsWith('storage/')) {
        path = `storage/${path}`;
    }
    
    return `${window.location.origin}/${path}`;
};

const toast = useToast();

const sharePost = async (post) => {
    // Asumiendo que existe una ruta para ver el post individual, si no, usamos la actual
    const url = route('posts.show', post.id); // Asegúrate de que esta ruta exista
    
    if (navigator.share) {
        try {
            await navigator.share({
                title: 'Publicación de GymPal',
                text: post.content,
                url: url
            });
        } catch (err) {
            // Usuario canceló o error
        }
    } else {
        navigator.clipboard.writeText(url);
        toast.success('Enlace copiado al portapapeles');
    }
};

const bannerInput = ref(null);

const updateBannerColor = (color) => {
    // Enviar mediante Inertia (usando PATCH en lugar de POST)
    router.patch(route('profile.updateBannerColor'), {
        banner_color: color
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showColorModal.value = false; // Cerrar modal
        },
        onError: () => {
            toast.error('Error al actualizar el banner');
        }
    });
};

// Bloquear scroll cuando el modal está abierto
watch(showColorModal, (val) => {
    if (val) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

/* FUNCIONALIDAD DE SUBIDA DE IMAGEN (COMENTADA - Para futuro)
const uploadBanner = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Crear FormData para enviar el archivo
    const formData = new FormData();
    formData.append('banner_picture', file);

    // Enviar mediante Inertia
    router.post(route('profile.update'), formData, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Banner actualizado correctamente');
        },
        onError: () => {
            toast.error('Error al actualizar el banner');
        },
        onFinish: () => {
            // Limpiar el input
            if (bannerInput.value) {
                bannerInput.value.value = '';
            }
        }
    });
};
*/

onMounted(() => {
    // Simulación de carga para mostrar el Skeleton
    if (props.profileUser) {
        setTimeout(() => isLoading.value = false, 400);
    }
});

// Funciones para abrir modal de conexiones
const openConnectionsModal = (type) => {
    connectionsModalType.value = type;
    showConnectionsModal.value = true;
};

const closeConnectionsModal = () => {
    showConnectionsModal.value = false;
};

const connect = () => {
    if (processingConnection.value) return;
    router.post(route('connections.store', props.profileUser.id), {}, {
        preserveScroll: true,
        preserveState: false, // Forzar recarga de props
        onStart: () => processingConnection.value = true,
        onFinish: () => processingConnection.value = false,
    });
};
const accept = () => {
    if (processingConnection.value) return;
    router.patch(route('connections.accept', props.connection_id), {}, {
        preserveScroll: true,
        preserveState: false,
        onStart: () => processingConnection.value = true,
        onFinish: () => processingConnection.value = false,
    });
};
const openRejectModal = () => {
    if (processingConnection.value) return;
    showRejectModal.value = true;
};

const confirmReject = () => {
    showRejectModal.value = false;
    router.patch(route('connections.reject', props.connection_id), {}, {
        preserveScroll: true,
        preserveState: false,
        onStart: () => processingConnection.value = true,
        onFinish: () => processingConnection.value = false,
    });
};

const openDisconnectModal = () => {
    if (processingConnection.value) return;
    showDisconnectModal.value = true;
};

const confirmDisconnect = () => {
    showDisconnectModal.value = false;
    router.delete(route('connections.destroy', props.connection_id), {
        preserveScroll: true,
        preserveState: false,
        onStart: () => processingConnection.value = true,
        onFinish: () => processingConnection.value = false,
    });
};

const sendMessage = () => {
    router.visit(route('chat.show', { user: props.profileUser.username }));
};

const avatarUrl = computed(() => {
    if (props.profileUser.profile_picture_url) {
        return `/storage/${props.profileUser.profile_picture_url}`;
    }
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(props.profileUser.name || 'G P')}&background=random&color=fff&size=160`;
});
const fitnessInterestsArray = computed(() => {
    return props.profileUser.fitness_interests || [];
});

// Helper para color de afinidad (si existe)
const getAffinityColor = (score) => {
    if (score >= 80) return 'from-green-400 to-emerald-600';
    if (score >= 50) return 'from-yellow-400 to-orange-500';
    return 'from-blue-400 to-indigo-500';
};

// Helper para color de nivel de experiencia
const getExperienceLevelColor = (level) => {
    const levelLower = level?.toLowerCase() || '';
    if (levelLower.includes('principiante') || levelLower.includes('beginner')) {
        return 'from-green-400 to-emerald-500';
    }
    if (levelLower.includes('intermedio') || levelLower.includes('intermediate')) {
        return 'from-blue-400 to-indigo-500';
    }
    if (levelLower.includes('avanzado') || levelLower.includes('advanced')) {
        return 'from-purple-500 to-pink-600';
    }
    return 'from-gray-400 to-gray-500'; // Fallback
};
</script>

<template>
    <Head :title="title" />
    <AuthenticatedLayout>
        
        <div v-if="isLoading">
            <ProfileSkeleton />
        </div>

        <div v-else-if="profileUser" class="container mx-auto px-4 py-8">
            <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden border border-gray-100 dark:border-gray-700 relative transition-colors">
                
                <!-- Badge de Afinidad (Si existe) -->
                <div v-if="profileUser.affinity_score > 0" 
                     class="absolute top-4 right-4 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg z-10 bg-gradient-to-r"
                     :class="getAffinityColor(profileUser.affinity_score)"
                >
                    {{ profileUser.affinity_score }}% Afinidad
                </div>

                <!-- Header Background -->
                <div 
                    class="h-32 relative group"
                    :style="{ backgroundColor: profileUser.banner_color || '#6366f1' }"
                >
                    <!-- Theme Switcher en esquina superior izquierda - Solo en perfil propio -->
                    <div v-if="isOwnProfile" class="absolute top-4 left-4 z-20">
                        <div class="bg-white/40 dark:bg-gray-900/50 backdrop-blur-md rounded-lg shadow-lg border border-white/60 dark:border-gray-700/60 hover:bg-white/50 dark:hover:bg-gray-900/60 transition-all">
                            <ThemeSwitcher />
                        </div>
                    </div>

                    <!-- Banner Image si existe (COMENTADO - Funcionalidad futura) -->
                    <!-- <img 
                        v-if="profileUser.banner_picture_url" 
                        :src="`/storage/${profileUser.banner_picture_url}`" 
                        class="w-full h-full object-cover"
                        alt="Banner"
                    /> -->
                    
                    <!-- Input color oculto -->
                    <input 
                        v-if="isOwnProfile"
                        ref="bannerInput"
                        type="color"
                        class="hidden"
                        :value="profileUser.banner_color || '#6366f1'"
                        @input="updateBannerColor"
                    />
                    
                    <!-- Input file oculto (COMENTADO - Funcionalidad futura) -->
                    <!-- <input 
                        v-if="isOwnProfile"
                        ref="bannerImageInput"
                        type="file"
                        accept="image/*"
                        class="hidden"
                        @change="uploadBanner"
                    /> -->
                    
                    <!-- Botón editar banner (Abre modal) -->
                    <button 
                        v-if="isOwnProfile"
                        @click="showColorModal = true"
                        class="absolute top-4 right-4 bg-white/20 backdrop-blur-md hover:bg-white/30 text-white p-2 rounded-full shadow-sm transition-all z-20"
                        title="Personalizar banner"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </button>
                </div>

                <div class="px-8 pb-8 flex flex-col items-center -mt-20">
                    <div class="relative">
                        <!-- Avatar con borde gradiente -->
                        <div class="w-36 h-36 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 p-1 shadow-lg">
                            <img :src="avatarUrl" :alt="profileUser.name" class="w-full h-full rounded-full object-cover border-4 border-white dark:border-gray-800 transition-colors" />
                        </div>
                        <!-- Nivel de Experiencia (Badge con gradiente dinámico) -->
                        <div v-if="profileUser.experience_level" 
                             class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 bg-gradient-to-r text-white text-xs font-bold uppercase tracking-wide px-3 py-1 rounded-full shadow-md"
                             :class="getExperienceLevelColor(profileUser.experience_level)"
                        >
                            {{ profileUser.experience_level }}
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3 mt-4 mb-1">
                        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight transition-colors">{{ profileUser.display_name || profileUser.name }}</h1>
                        <span v-if="is_following_me" class="bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 text-[10px] font-black px-2 py-1 rounded-full uppercase tracking-wider h-fit">Te sigue</span>
                    </div>
                    <p v-if="profileUser.username" class="text-md text-indigo-600 dark:text-indigo-400 font-medium mb-4 transition-colors">@{{ profileUser.username }}</p>
                    
                    <!-- Ubicación -->
                    <div v-if="profileUser.location_city" class="flex items-center gap-1 text-gray-500 dark:text-gray-400 text-sm mb-4 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        {{ profileUser.location_city }}
                    </div>

                    <p v-if="profileUser.bio" class="text-gray-600 dark:text-gray-300 text-center mb-6 whitespace-pre-line max-w-lg leading-relaxed italic transition-colors">"{{ profileUser.bio }}"</p>

                    <!-- Stats -->
                    <div class="flex gap-4 sm:gap-8 justify-center mb-8 w-full border-t border-b border-gray-100 dark:border-gray-700 py-4 transition-colors">
                        <button @click="openConnectionsModal('gymPals')" class="text-center px-2 sm:px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors cursor-pointer">
                            <span class="block text-xl sm:text-2xl font-bold text-gray-800 dark:text-white transition-colors">{{ gym_pals_count ?? 0 }}</span>
                            <span class="text-[10px] sm:text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider transition-colors">GymPals</span>
                        </button>
                        <button @click="openConnectionsModal('followers')" class="text-center border-l border-gray-100 dark:border-gray-700 px-2 sm:px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors cursor-pointer">
                            <span class="block text-xl sm:text-2xl font-bold text-gray-800 dark:text-white transition-colors">{{ followers_count ?? 0 }}</span>
                            <span class="text-[10px] sm:text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider transition-colors">Seguidores</span>
                        </button>
                        <button @click="openConnectionsModal('following')" class="text-center border-l border-gray-100 dark:border-gray-700 px-2 sm:px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors cursor-pointer">
                            <span class="block text-xl sm:text-2xl font-bold text-gray-800 dark:text-white transition-colors">{{ following_count ?? 0 }}</span>
                            <span class="text-[10px] sm:text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider transition-colors">Siguiendo</span>
                        </button>
                    </div>

                    <!-- Sección de Gimnasios (Nuevo) -->
                    <div v-if="profileUser.gyms && profileUser.gyms.length > 0" class="w-full mb-8 text-center animate-fade-in-up">
                        <div class="inline-block bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-gray-600 transition-colors">
                            <h3 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3 transition-colors">Entrena en</h3>
                            <div class="flex flex-wrap justify-center gap-4">
                                <!-- Gyms Visibles (Max 4) -->
                                <div v-for="gym in displayedGyms" :key="gym.id" class="flex flex-col items-center group cursor-pointer" @click="openGymModal(gym.id)">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/30 dark:to-indigo-800/30 flex items-center justify-center text-2xl shadow-sm group-hover:scale-110 transition-transform mb-1 border border-indigo-200 dark:border-indigo-700 group-hover:border-indigo-500">
                                        {{ gym.type === 'pool' ? '🏊' : (gym.type === 'yoga' ? '🧘' : (gym.type === 'crossfit' ? '🔥' : (gym.type === 'park' ? '🤸' : '🏋️'))) }}
                                    </div>
                                    <span class="text-[10px] font-bold text-gray-700 dark:text-gray-300 max-w-[80px] truncate">{{ gym.name }}</span>
                                </div>

                                <!-- Botón Ver Más -->
                                <div v-if="remainingGymsCount > 0" class="flex flex-col items-center group cursor-pointer" @click="showAllGymsModal = true">
                                    <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-sm font-bold text-gray-500 dark:text-gray-400 shadow-sm group-hover:bg-gray-200 dark:group-hover:bg-gray-600 transition-colors mb-1 border border-gray-200 dark:border-gray-600">
                                        +{{ remainingGymsCount }}
                                    </div>
                                    <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400">Ver más</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Secciones de Información -->
                    <div class="w-full space-y-6 mb-8">
                        <!-- Intereses -->
                        <div v-if="fitnessInterestsArray.length" class="text-center">
                            <h3 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3 transition-colors">Intereses / Deportes</h3>
                            <div class="flex flex-wrap gap-2 justify-center">
                                <span v-for="interest in fitnessInterestsArray" :key="interest.id" class="inline-block bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800 text-sm font-medium px-3 py-1 rounded-full transition-colors">
                                    {{ interest.name }}
                                </span>
                            </div>
                        </div>

                        <!-- Disponibilidad -->
                        <div v-if="profileUser.availability_general && profileUser.availability_general.length" class="text-center">
                            <h3 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3 transition-colors">Disponibilidad Habitual</h3>
                            <div class="flex flex-wrap gap-2 justify-center">
                                <span v-for="slot in profileUser.availability_general" :key="slot" class="inline-block bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-100 dark:border-green-800 text-sm font-medium px-3 py-1 rounded-lg transition-colors">
                                    {{ slot }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div v-if="!isOwnProfile" class="flex flex-wrap justify-center gap-3 w-full mb-8">
                        <div v-if="connection_status === 'none'" class="flex flex-col items-center gap-2 w-full">
                            <button @click="connect" :disabled="processingConnection" class="btn-primary-gradient">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                {{ is_following_me ? 'Seguir también' : 'Conectar' }}
                            </button>
                            <span v-if="is_following_me" class="text-xs text-gray-500 dark:text-gray-400 font-medium transition-colors">
                                {{ profileUser.name.split(' ')[0] }} te sigue
                            </span>
                        </div>

                        <button v-else-if="connection_status === 'sent'" disabled class="btn-disabled">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Solicitud Enviada
                        </button>

                        <div v-else-if="connection_status === 'received'" class="flex gap-2">
                            <button @click="accept" :disabled="processingConnection" class="btn-primary-gradient">
                                Aceptar
                            </button>
                            <button @click="openRejectModal" :disabled="processingConnection" class="btn-danger-gradient">
                                Rechazar
                            </button>
                        </div>

                        <div v-else-if="connection_status === 'accepted'" class="flex gap-2">
                            <button @click="sendMessage" class="btn-primary-gradient">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                Mensaje
                            </button>
                            <button @click="openDisconnectModal" :disabled="processingConnection" class="btn-secondary-gradient">
                                Desconectar
                            </button>
                        </div>
                    </div>
                    
                    <!-- Botón de Estadísticas (Solo para GymPals o perfil propio) -->
                    <div v-if="isOwnProfile || (are_gym_pals && connection_status === 'accepted')" class="w-full max-w-sm mx-auto px-4 mb-6">
                        <Link 
                            :href="isOwnProfile ? route('stats.index') : route('stats.show', profileUser.username)" 
                            class="w-full bg-gradient-to-r from-yellow-500 to-orange-600 text-white font-bold py-3 px-4 rounded-xl text-center shadow-lg flex items-center justify-center gap-2 hover:from-yellow-600 hover:to-orange-700 transition-all active:scale-95"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            {{ isOwnProfile ? 'Ver mis Estadísticas' : `Ver Estadísticas de ${profileUser.name.split(' ')[0]}` }}
                        </Link>
                    </div>
                    
                    <div v-if="isOwnProfile" class="flex flex-col gap-3 w-full max-w-sm mx-auto px-4">
                        <Link :href="route('profile.edit')" class="w-full bg-white dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 font-bold py-3 px-4 rounded-xl text-center shadow-sm flex items-center justify-center gap-2 hover:border-indigo-300 dark:hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all active:scale-95">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                            Editar Perfil
                        </Link>
                        <button @click="showLogoutModal = true" class="w-full bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 font-bold py-3 px-4 rounded-xl text-center shadow-sm flex items-center justify-center gap-2 hover:bg-red-100 dark:hover:bg-red-900/40 transition active:scale-95 border border-transparent hover:border-red-200 dark:hover:border-red-800">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                            Cerrar Sesión
                        </button>
                    </div>

                    <div v-if="profileUser.created_at" class="mt-8 text-xs text-gray-400">
                        Miembro desde {{ new Date(profileUser.created_at).toLocaleDateString('es-ES', { month: 'long', year: 'numeric' }) }}
                    </div>
                </div>
            </div>


            <!-- Sección de Contenido con Tabs -->
            <div v-if="isOwnProfile || connection_status === 'accepted'" class="max-w-2xl mx-auto mt-8 px-4 sm:px-0">
                
                <!-- Tabs Navigation Moderno -->
                <div class="flex justify-center mb-8">
                    <div class="bg-gray-100/80 dark:bg-gray-700/80 backdrop-blur-sm p-1.5 rounded-2xl inline-flex shadow-inner transition-colors">
                        <button
                            @click="activeContentTab = 'rutinas'"
                            class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 flex items-center gap-2 relative overflow-hidden"
                            :class="activeContentTab === 'rutinas' ? 'bg-white dark:bg-gray-600 text-indigo-600 dark:text-indigo-300 shadow-sm ring-1 ring-black/5 dark:ring-white/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                        >
                            <span class="relative z-10 flex items-center gap-2">
                                🏋️ Rutinas
                                <span v-if="workouts.length" class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="activeContentTab === 'rutinas' ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300' : 'bg-gray-200 dark:bg-gray-600 text-gray-500 dark:text-gray-400'">
                                    {{ workouts.length }}
                                </span>
                            </span>
                        </button>
                        
                        <button
                            @click="activeContentTab = 'publicaciones'"
                            class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 flex items-center gap-2 relative overflow-hidden"
                            :class="activeContentTab === 'publicaciones' ? 'bg-white dark:bg-gray-600 text-indigo-600 dark:text-indigo-300 shadow-sm ring-1 ring-black/5 dark:ring-white/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                        >
                            <span class="relative z-10 flex items-center gap-2">
                                📰 Publicaciones
                                <span v-if="posts.length" class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="activeContentTab === 'publicaciones' ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300' : 'bg-gray-200 dark:bg-gray-600 text-gray-500 dark:text-gray-400'">
                                    {{ posts.length }}
                                </span>
                            </span>
                        </button>

                        <button
                            @click="activeContentTab = 'estadisticas'"
                            class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 flex items-center gap-2 relative overflow-hidden"
                            :class="activeContentTab === 'estadisticas' ? 'bg-white dark:bg-gray-600 text-indigo-600 dark:text-indigo-300 shadow-sm ring-1 ring-black/5 dark:ring-white/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                        >
                            <span class="relative z-10 flex items-center gap-2">
                                📊 Stats
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Workouts Tab -->
                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-2"
                    mode="out-in"
                >
                    <div v-if="activeContentTab === 'rutinas'" key="rutinas">
                        <div v-if="workouts.length > 0">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Tarjetas de Rutina Compactas -->
                                <div 
                                    v-for="workout in displayedWorkouts" 
                                    :key="workout.id" 
                                    @click="$inertia.visit(route('workouts.show', workout.id))"
                                    class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-500 transition-all cursor-pointer group flex flex-col justify-between h-full relative overflow-hidden"
                                >
                                    <!-- Banda lateral de dificultad -->
                                    <div 
                                        class="absolute left-0 top-0 bottom-0 w-1.5"
                                        :class="{
                                            'bg-emerald-400': workout.difficulty === 'Principiante',
                                            'bg-blue-500': workout.difficulty === 'Intermedio',
                                            'bg-purple-500': workout.difficulty === 'Avanzado'
                                        }"
                                    ></div>

                                    <div class="pl-3">
                                        <div class="flex justify-between items-start mb-2">
                                            <h3 class="font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-1 text-sm">{{ workout.name }}</h3>
                                            <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase">{{ workout.difficulty }}</span>
                                        </div>
                                        
                                        <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                            <span class="flex items-center gap-1 bg-gray-50 dark:bg-gray-700 px-2 py-1 rounded-md transition-colors">
                                                ⏱️ {{ workout.duration_minutes }}'
                                            </span>
                                            <span class="flex items-center gap-1 bg-gray-50 dark:bg-gray-700 px-2 py-1 rounded-md transition-colors">
                                                💪 {{ workout.exercises?.length || 0 }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón Ver Más/Menos Rutinas -->
                            <div v-if="workouts.length > 4" class="mt-4 text-center">
                                <button 
                                    v-if="!showAllWorkouts"
                                    @click="showAllWorkouts = true"
                                    class="text-sm font-bold text-indigo-600 hover:text-indigo-800 transition flex items-center justify-center gap-1 mx-auto"
                                >
                                    Ver todas las rutinas ({{ workouts.length }})
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                                <button 
                                    v-else
                                    @click="showAllWorkouts = false"
                                    class="text-sm font-bold text-indigo-600 hover:text-indigo-800 transition flex items-center justify-center gap-1 mx-auto"
                                >
                                    Ver menos
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Empty State Rutinas -->
                        <div v-else class="text-center py-12 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm transition-colors">
                            <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 dark:text-indigo-400 rounded-xl flex items-center justify-center mx-auto mb-3 text-xl transition-colors">
                                🏋️
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 transition-colors">Sin rutinas públicas</p>
                            
                            <button 
                                v-if="isOwnProfile"
                                @click="$inertia.visit(route('workouts.create'))"
                                class="px-4 py-2 bg-gray-900 dark:bg-gray-700 text-white text-xs rounded-lg font-bold hover:bg-gray-800 dark:hover:bg-gray-600 transition-colors"
                            >
                                Crear Rutina
                            </button>
                        </div>

                        <!-- Botón crear nueva (si hay rutinas) -->
                        <div v-if="isOwnProfile && workouts.length > 0" class="mt-4">
                            <button 
                                @click="$inertia.visit(route('workouts.create'))"
                                class="w-full py-3 bg-white dark:bg-gray-800 border border-dashed border-gray-300 dark:border-gray-600 hover:border-indigo-400 dark:hover:border-indigo-500 text-gray-400 dark:text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2 hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10"
                            >
                                + Nueva Rutina
                            </button>
                        </div>
                    </div>

                    <!-- Posts Tab (Masonry Layout) -->
                    <div v-else-if="activeContentTab === 'publicaciones'" key="publicaciones">
                        <div v-if="posts && posts.length > 0" class="columns-2 md:columns-3 gap-3 space-y-3">
                            <div 
                                v-for="post in posts" 
                                :key="post.id" 
                                class="break-inside-avoid bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all group relative"
                            >
                                <!-- CASO 1: Post con Imagen (Estilo Instagram) -->
                                <div v-if="post.image_path" class="relative cursor-pointer" @click="openPostModal(post)">
                                    <img 
                                        :src="post.image_path.startsWith('http') ? post.image_path : `/storage/${post.image_path}`" 
                                        class="w-full h-auto object-cover"
                                        loading="lazy"
                                    />
                                    
                                    <!-- Badge de Rutina -->
                                    <div v-if="post.workout" class="absolute top-2 left-2 bg-black/50 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-1 rounded-full flex items-center gap-1">
                                        <span>🏋️</span> Rutina
                                    </div>
                                </div>



                                <!-- CASO 2: Post solo Texto (Estilo Twitter) -->
                                <div v-else class="p-4 flex flex-col h-full cursor-pointer" @click="openPostModal(post)">
                                    <p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed font-medium mb-3 flex-1 transition-colors">{{ post.content }}</p>
                                    
                                    <!-- Link a Rutina si existe -->
                                    <div 
                                        v-if="post.workout" 
                                        @click.stop="$inertia.visit(route('workouts.show', post.workout.id))"
                                        class="mb-3 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-lg p-2 flex items-center gap-2 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition cursor-pointer"
                                    >
                                        <div class="w-8 h-8 bg-indigo-200 dark:bg-indigo-800 rounded-md flex items-center justify-center text-indigo-700 dark:text-indigo-300 text-xs">🏋️</div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-indigo-900 dark:text-indigo-200 truncate">{{ post.workout.name }}</p>
                                            <p class="text-[10px] text-indigo-600 dark:text-indigo-400">Ver rutina</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between text-xs text-gray-400 border-t border-gray-50 pt-3 mt-auto">
                                        <div class="flex items-center gap-3">
                                            <span class="flex items-center gap-1 transition-colors" :class="post.is_liked ? 'text-red-500' : 'hover:text-pink-500'">
                                                <svg class="w-4 h-4" :class="post.is_liked ? 'fill-red-500 text-red-500' : 'fill-none stroke-current'" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                                                {{ post.likes_count || 0 }}
                                            </span>
                                            <span class="flex items-center gap-1 hover:text-blue-500 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                                {{ post.comments_count || 0 }}
                                            </span>
                                        </div>
                                        
                                        <button 
                                            @click.stop="sharePost(post)"
                                            class="hover:text-gray-600 transition p-1 -mr-1 rounded-full hover:bg-gray-100"
                                            title="Compartir"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Empty State Publicaciones -->
                        <div v-else class="text-center py-12 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm transition-colors">
                            <div class="w-12 h-12 bg-pink-50 dark:bg-pink-900/30 text-pink-500 dark:text-pink-400 rounded-xl flex items-center justify-center mx-auto mb-3 text-xl transition-colors">
                                📷
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 transition-colors">Sin publicaciones</p>
                            
                            <button 
                                v-if="isOwnProfile"
                                @click="$inertia.visit(route('posts.create'))"
                                class="px-4 py-2 bg-gray-900 dark:bg-gray-700 text-white text-xs rounded-lg font-bold hover:bg-gray-800 dark:hover:bg-gray-600 transition-colors"
                            >
                                Crear Post
                            </button>
                        </div>
                    </div>

                    <!-- Stats Tab -->
                    <div v-else-if="activeContentTab === 'estadisticas'" key="estadisticas">
                        <ProfileStats 
                            :stats="stats" 
                            :show-chart="true"
                            :progress-charts="progressCharts"
                        />
                    </div>
                </Transition>
            </div>
                    
                    <!-- Empty State for Posts -->


            
            <div v-else class="max-w-2xl mx-auto mt-8 text-center py-12 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 px-6 transition-colors">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 transition-colors">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 transition-colors">Este perfil es privado</h3>
                <p class="text-gray-500 dark:text-gray-400 transition-colors">Conecta con {{ profileUser.name }} para ver sus publicaciones y actividad reciente.</p>
                <button v-if="connection_status === 'none'" @click="connect" :disabled="processingConnection" class="mt-6 btn-primary-gradient">
                    Conectar ahora
                </button>
            </div>

        </div>

        <!-- Modal de Post Grid -->
        <PostGridModal
            v-if="showPostModal && posts && posts.length > 0"
            :posts="posts"
            :initialPostIndex="selectedPostIndex"
            @close="showPostModal = false"
        />

        <!-- Modal de confirmación de rechazo -->
        <ConfirmModal
            :show="showRejectModal"
            type="warning"
            title="¿Rechazar solicitud?"
            message="¿Estás seguro de que quieres rechazar esta solicitud de conexión?"
            confirm-text="Sí, rechazar"
            cancel-text="Cancelar"
            @confirm="confirmReject"
            @cancel="showRejectModal = false"
        />

        <!-- Modal de confirmación de desconexión -->
        <ConfirmModal
            :show="showDisconnectModal"
            type="danger"
            title="¿Desconectar?"
            :message="`¿Estás seguro de que quieres desconectar de ${profileUser.name}? Tendrás que enviar una nueva solicitud para volver a conectar.`"
            confirm-text="Sí, desconectar"
            cancel-text="Cancelar"
            @confirm="confirmDisconnect"
            @cancel="showDisconnectModal = false"
        />

        <!-- Modal de Selección de Color (Personalizado) -->
        <div v-if="showColorModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="showColorModal = false"></div>
            
            <!-- Modal Content -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden animate-bounce-in transition-colors">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700/50 transition-colors">
                    <h3 class="text-lg font-black text-gray-900 dark:text-white transition-colors">Personalizar Banner</h3>
                    <button @click="showColorModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition p-1 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 font-medium transition-colors">Elige un color que represente tu estilo:</p>
                    
                    <div class="grid grid-cols-5 gap-3">
                        <button 
                            v-for="color in bannerColors" 
                            :key="color"
                            @click="updateBannerColor(color)"
                            class="w-12 h-12 rounded-full shadow-sm hover:scale-110 transition-transform duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 relative group"
                            :style="{ backgroundColor: color }"
                            :title="color"
                        >
                            <!-- Check si es el color actual -->
                            <span v-if="profileUser.banner_color === color" class="absolute inset-0 flex items-center justify-center text-white">
                                <svg class="w-6 h-6 drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Conexiones -->
        <div v-if="showConnectionsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeConnectionsModal">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="closeConnectionsModal"></div>
            
            <!-- Modal Content -->
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full max-h-[80vh] overflow-hidden transition-colors">
                <!-- Header -->
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between z-10 transition-colors">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white transition-colors">
                        {{ connectionsModalType === 'gymPals' ? 'GymPals' : connectionsModalType === 'followers' ? 'Seguidores' : 'Siguiendo' }}
                    </h3>
                    <button @click="closeConnectionsModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- List -->
                <div class="overflow-y-auto max-h-[calc(80vh-80px)] p-4">
                    <template v-if="connectionsModalType === 'gymPals'">
                        <div v-if="gym_pals_list && gym_pals_list.length > 0" class="space-y-2">
                            <Link
                                v-for="user in gym_pals_list"
                                :key="user.id"
                                :href="route('profile.show.public', user.username)"
                                class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                @click="closeConnectionsModal"
                            >
                                <img :src="getProfilePictureUrl(user)" :alt="user.name" class="w-12 h-12 rounded-full object-cover" />
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="font-bold text-gray-900 dark:text-white truncate transition-colors">{{ user.name }}</p>
                                        <span v-if="user.is_following_me" class="bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 text-[9px] font-black px-1.5 py-0.5 rounded-full uppercase tracking-tighter">Te sigue</span>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate transition-colors">@{{ user.username }}</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                        <div v-else class="text-center py-12">
                            <p class="text-gray-500 dark:text-gray-400 transition-colors">No tienes GymPals aún</p>
                        </div>
                    </template>

                    <template v-else-if="connectionsModalType === 'followers'">
                        <div v-if="followers_list && followers_list.length > 0" class="space-y-2">
                            <Link
                                v-for="user in followers_list"
                                :key="user.id"
                                :href="route('profile.show.public', user.username)"
                                class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                @click="closeConnectionsModal"
                            >
                                <img :src="getProfilePictureUrl(user)" :alt="user.name" class="w-12 h-12 rounded-full object-cover" />
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="font-bold text-gray-900 dark:text-white truncate transition-colors">{{ user.name }}</p>
                                        <span v-if="user.is_following_me" class="bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 text-[9px] font-black px-1.5 py-0.5 rounded-full uppercase tracking-tighter">Te sigue</span>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate transition-colors">@{{ user.username }}</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                        <div v-else class="text-center py-12">
                            <p class="text-gray-500 dark:text-gray-400 transition-colors">No tienes seguidores aún</p>
                        </div>
                    </template>

                    <template v-else-if="connectionsModalType === 'following'">
                        <div v-if="following_list && following_list.length > 0" class="space-y-2">
                            <Link
                                v-for="user in following_list"
                                :key="user.id"
                                :href="route('profile.show.public', user.username)"
                                class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                @click="closeConnectionsModal"
                            >
                                <img :src="getProfilePictureUrl(user)" :alt="user.name" class="w-12 h-12 rounded-full object-cover" />
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="font-bold text-gray-900 dark:text-white truncate transition-colors">{{ user.name }}</p>
                                        <span v-if="user.is_following_me" class="bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 text-[9px] font-black px-1.5 py-0.5 rounded-full uppercase tracking-tighter">Te sigue</span>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate transition-colors">@{{ user.username }}</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                        <div v-else class="text-center py-12">
                            <p class="text-gray-500 dark:text-gray-400 transition-colors">No sigues a nadie aún</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Modal para ver todos los gimnasios -->
        <Modal :show="showAllGymsModal" @close="showAllGymsModal = false">
            <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="text-2xl">🏋️</span>
                        Centros de Entrenamiento
                    </h3>
                    <button @click="showAllGymsModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-3 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                    <div v-for="gym in profileUser.gyms" :key="gym.id" 
                         class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-700/30 hover:bg-white dark:hover:bg-gray-700 border border-transparent hover:border-indigo-200 dark:hover:border-indigo-500 hover:shadow-md transition-all cursor-pointer group"
                         @click="openGymModal(gym.id)">
                        
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-100 to-white dark:from-indigo-900/50 dark:to-gray-800 flex items-center justify-center text-2xl shadow-sm border border-indigo-50 dark:border-indigo-800 group-hover:scale-110 transition-transform">
                            {{ gym.type === 'pool' ? '🏊' : (gym.type === 'yoga' ? '🧘' : (gym.type === 'crossfit' ? '🔥' : (gym.type === 'park' ? '🤸' : '🏋️'))) }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-gray-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ gym.name }}</h4>
                            <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <span class="truncate">{{ gym.address || 'Sin dirección' }}</span>
                            </div>
                        </div>

                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-600 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
            </div>
        </Modal>

        <GymDetailsModal 
            :show="showGymModal" 
            :gym-id="selectedGymId" 
            @close="showGymModal = false"
            @view-all-members="openGymMembersModal"
        />

        <!-- Logout Confirmation Modal -->
        <ConfirmModal
            :show="showLogoutModal"
            type="danger"
            title="¿Cerrar Sesión?"
            message="¿Estás seguro de que quieres salir de GymPal?"
            confirm-text="Cerrar Sesión"
            cancel-text="Cancelar"
            @confirm="confirmLogout"
            @cancel="showLogoutModal = false"
        />
        <!-- Modal para ver todos los gimnasios -->
        <Modal :show="showAllGymsModal" @close="showAllGymsModal = false">
            <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="text-2xl">🏋️</span>
                        Centros de Entrenamiento
                    </h3>
                    <button @click="showAllGymsModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-3 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                    <div v-for="gym in profileUser.gyms" :key="gym.id" 
                         class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-700/30 hover:bg-white dark:hover:bg-gray-700 border border-transparent hover:border-indigo-200 dark:hover:border-indigo-500 hover:shadow-md transition-all cursor-pointer group"
                         @click="openGymModal(gym.id)">
                        
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-100 to-white dark:from-indigo-900/50 dark:to-gray-800 flex items-center justify-center text-2xl shadow-sm border border-indigo-50 dark:border-indigo-800 group-hover:scale-110 transition-transform">
                            {{ gym.type === 'pool' ? '🏊' : (gym.type === 'yoga' ? '🧘' : (gym.type === 'crossfit' ? '🔥' : (gym.type === 'park' ? '🤸' : '🏋️'))) }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-gray-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ gym.name }}</h4>
                            <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <span class="truncate">{{ gym.address || 'Sin dirección' }}</span>
                            </div>
                        </div>

                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-600 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Gym Members Modal -->
        <GymMembersModal
            :show="showGymMembersModal"
            :gym="selectedGymForMembers"
            @close="showGymMembersModal = false"
        />

    </AuthenticatedLayout>
</template>

<style scoped>
.btn-primary { @apply inline-flex items-center justify-center px-4 py-3 bg-indigo-600 border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 transition disabled:opacity-50; }
.btn-primary-gradient {
    @apply inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:from-indigo-700 hover:to-purple-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:-translate-y-0.5;
}
.btn-secondary { @apply inline-flex items-center justify-center px-4 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-bold text-sm text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 transition disabled:opacity-50; }
.btn-secondary-gradient {
    @apply inline-flex items-center px-6 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-full font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:text-gray-500 dark:hover:text-gray-400 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition ease-in-out duration-150 hover:shadow-md transform hover:-translate-y-0.5;
}
.btn-danger-gradient {
    @apply inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-red-500 to-pink-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:from-red-600 hover:to-pink-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:-translate-y-0.5;
}
.btn-disabled {
    @apply inline-flex items-center px-6 py-2.5 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-full font-semibold text-xs text-white dark:text-gray-400 uppercase tracking-widest cursor-not-allowed transition-colors;
}
</style>