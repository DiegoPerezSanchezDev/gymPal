
<script setup>
// Importaciones y props
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProfileSkeleton from '@/Components/Skeletons/ProfileSkeleton.vue';
import PostCard from '@/Components/PostCard.vue';
import ConnectionsModal from '@/Components/ConnectionsModal.vue';
import PostGridModal from '@/Components/PostGridModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';

// Props que recibe el perfil público
const props = defineProps({
    profileUser: Object,
    title: String,
    isOwnProfile: Boolean,
    connection_status: String,
    connection_id: Number,
    connections_count: Number,
    connections_list: Array, // Lista de conexiones para el modal
    posts: Array, // Posts del usuario
    workouts: {
        type: Array,
        default: () => []
    }, // Rutinas del usuario
});

const processingConnection = ref(false);
const isLoading = ref(true);
const showConnectionsModal = ref(false);
const showPostModal = ref(false);
const selectedPostIndex = ref(0);
const showRejectModal = ref(false);
const showDisconnectModal = ref(false);

// Detectar tab desde URL
const urlParams = new URLSearchParams(window.location.search);
const tabParam = urlParams.get('tab');
const activeContentTab = ref(tabParam === 'rutinas' ? 'rutinas' : 'publicaciones');

const openPostModal = (post) => {
    // Find the index in the original posts array
    const index = props.posts.findIndex(p => p.id === post.id);
    if (index !== -1) {
        selectedPostIndex.value = index;
        showPostModal.value = true;
    }
};

onMounted(() => {
    // Simulación de carga para mostrar el Skeleton
    if (props.profileUser) {
        setTimeout(() => isLoading.value = false, 400);
    }
});

const connect = () => {
    if (processingConnection.value) return;
    router.post(route('connections.store', props.profileUser.id), {}, {
        preserveScroll: true,
        onStart: () => processingConnection.value = true,
        onFinish: () => processingConnection.value = false,
    });
};
const accept = () => {
    if (processingConnection.value) return;
    router.patch(route('connections.accept', props.connection_id), {}, {
        preserveScroll: true,
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
            <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 relative">
                
                <!-- Badge de Afinidad (Si existe) -->
                <div v-if="profileUser.affinity_score > 0" 
                     class="absolute top-4 right-4 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg z-10 bg-gradient-to-r"
                     :class="getAffinityColor(profileUser.affinity_score)"
                >
                    {{ profileUser.affinity_score }}% Afinidad
                </div>

                <!-- Header Background -->
                <div class="h-32 bg-gradient-to-r from-indigo-500 to-purple-600"></div>

                <div class="px-8 pb-8 flex flex-col items-center -mt-20">
                    <div class="relative">
                        <!-- Avatar con borde gradiente -->
                        <div class="w-36 h-36 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 p-1 shadow-lg">
                            <img :src="avatarUrl" :alt="profileUser.name" class="w-full h-full rounded-full object-cover border-4 border-white" />
                        </div>
                        <!-- Nivel de Experiencia (Badge con gradiente dinámico) -->
                        <div v-if="profileUser.experience_level" 
                             class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 bg-gradient-to-r text-white text-xs font-bold uppercase tracking-wide px-3 py-1 rounded-full shadow-md"
                             :class="getExperienceLevelColor(profileUser.experience_level)"
                        >
                            {{ profileUser.experience_level }}
                        </div>
                    </div>
                    
                    <h1 class="text-3xl font-extrabold text-gray-900 mt-4 mb-1 tracking-tight text-center">{{ profileUser.display_name || profileUser.name }}</h1>
                    <p v-if="profileUser.username" class="text-md text-indigo-600 font-medium mb-4">@{{ profileUser.username }}</p>
                    
                    <!-- Ubicación -->
                    <div v-if="profileUser.location_city" class="flex items-center gap-1 text-gray-500 text-sm mb-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        {{ profileUser.location_city }}
                    </div>

                    <p v-if="profileUser.bio" class="text-gray-600 text-center mb-6 whitespace-pre-line max-w-lg leading-relaxed italic">"{{ profileUser.bio }}"</p>

                    <!-- Stats -->
                    <div class="flex gap-8 justify-center mb-8 w-full border-t border-b border-gray-100 py-4">
                        <button @click="showConnectionsModal = true" class="text-center hover:bg-gray-50 rounded-lg px-4 py-2 transition-colors cursor-pointer">
                            <span class="block text-2xl font-bold text-gray-800">{{ connections_count ?? 0 }}</span>
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Conexiones</span>
                        </button>
                        <div class="text-center border-l border-gray-100 pl-8 py-2">
                            <span class="block text-2xl font-bold text-gray-800">{{ profileUser.posts_count ?? 0 }}</span>
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Publicaciones</span>
                        </div>
                    </div>

                    <!-- Secciones de Información -->
                    <div class="w-full space-y-6 mb-8">
                        <!-- Intereses -->
                        <div v-if="fitnessInterestsArray.length" class="text-center">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Intereses / Deportes</h3>
                            <div class="flex flex-wrap gap-2 justify-center">
                                <span v-for="interest in fitnessInterestsArray" :key="interest.id" class="inline-block bg-indigo-50 text-indigo-700 border border-indigo-100 text-sm font-medium px-3 py-1 rounded-full">
                                    {{ interest.name }}
                                </span>
                            </div>
                        </div>

                        <!-- Disponibilidad -->
                        <div v-if="profileUser.availability_general && profileUser.availability_general.length" class="text-center">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Disponibilidad Habitual</h3>
                            <div class="flex flex-wrap gap-2 justify-center">
                                <span v-for="slot in profileUser.availability_general" :key="slot" class="inline-block bg-green-50 text-green-700 border border-green-100 text-sm font-medium px-3 py-1 rounded-lg">
                                    {{ slot }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div v-if="!isOwnProfile" class="flex flex-wrap justify-center gap-3 w-full">
                        <button v-if="connection_status === 'none'" @click="connect" :disabled="processingConnection" class="btn-primary-gradient">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                            Conectar
                        </button>

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
                    
                    <div v-else class="flex justify-center w-full gap-3">
                        <Link :href="route('profile.edit')" class="btn-secondary-gradient">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                            Editar Perfil
                        </Link>
                        <Link :href="route('logout')" method="post" as="button" class="flex-1 bg-red-50 text-red-600 font-bold py-3 px-4 rounded-xl text-center shadow-sm flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                            Salir
                        </Link>
                    </div>

                    <div v-if="profileUser.created_at" class="mt-8 text-xs text-gray-400">
                        Miembro desde {{ new Date(profileUser.created_at).toLocaleDateString('es-ES', { month: 'long', year: 'numeric' }) }}
                    </div>
                </div>
            </div>


            <!-- Sección de Contenido con Tabs -->
            <div v-if="isOwnProfile || connection_status === 'accepted'" class="max-w-2xl mx-auto mt-8">
                
                <!-- Tabs Navigation -->
                <div class="flex items-center justify-between mb-6 px-2">
                    <div class="flex gap-2 bg-white rounded-xl p-1 shadow-sm border border-gray-100">
                        <button
                            @click="activeContentTab = 'rutinas'"
                            :class="[
                                'px-6 py-2.5 rounded-lg font-bold text-sm transition-all duration-200 flex items-center gap-2',
                                activeContentTab === 'rutinas' 
                                    ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-md' 
                                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                            ]"
                        >
                            🏋️ Rutinas
                            <span v-if="workouts.length" :class="activeContentTab === 'rutinas' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700'" class="px-2 py-0.5 rounded-full text-xs font-bold">
                                {{ workouts.length }}
                            </span>
                        </button>
                        
                        <button
                            @click="activeContentTab = 'publicaciones'"
                            :class="[
                                'px-6 py-2.5 rounded-lg font-bold text-sm transition-all duration-200 flex items-center gap-2',
                                activeContentTab === 'publicaciones' 
                                    ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-md' 
                                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                            ]"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                            </svg>
                            Publicaciones
                            <span :class="activeContentTab === 'publicaciones' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700'" class="px-2 py-0.5 rounded-full text-xs font-bold">
                                {{ posts.length }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Workouts Tab -->
                <div v-if="activeContentTab === 'rutinas'">
                    <div v-if="workouts.length > 0" class="grid grid-cols-1 gap-4">
                        <!-- Workout cards will go here -->
                        <div 
                            v-for="workout in workouts" 
                            :key="workout.id" 
                            @click="$inertia.visit(route('workouts.show', workout.id))"
                            class="bg-white border-2 border-gray-100 rounded-2xl p-5 hover:border-indigo-200 hover:shadow-lg transition-all cursor-pointer group"
                        >
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h3 class="font-bold text-lg text-gray-900 group-hover:text-indigo-600 transition-colors">{{ workout.name }}</h3>
                                    <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ workout.description }}</p>
                                </div>
                                <span 
                                    class="px-3 py-1 rounded-full text-xs font-bold capitalize"
                                    :class="{
                                        'bg-emerald-100 text-emerald-700': workout.difficulty_level === 'principiante',
                                        'bg-indigo-100 text-indigo-700': workout.difficulty_level === 'intermedio',
                                        'bg-purple-100 text-purple-700': workout.difficulty_level === 'avanzado'
                                    }"
                                >
                                    {{ workout.difficulty_level }}
                                </span>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-gray-600">
                                <span class="flex items-center gap-1">
                                    ⏱️ {{ workout.duration_minutes }} min
                                </span>
                                <span class="flex items-center gap-1">
                                    💪 {{ workout.exercises?.length || 0 }} ejercicios
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Empty State -->
                    <div v-else class="text-center py-12 bg-white rounded-2xl border-2 border-dashed border-gray-200">
                        <div class="w-16 h-16 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                            🏋️
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">No hay rutinas públicas</h3>
                        <p class="text-gray-500 mb-6 max-w-xs mx-auto">Comparte tus entrenamientos con la comunidad de GymPal.</p>
                        
                        <button 
                            v-if="isOwnProfile"
                            @click="$inertia.visit(route('workouts.create'))"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-full font-bold hover:bg-indigo-700 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                        >
                            Crear mi primera rutina
                        </button>
                    </div>

                    <!-- Button to create new workout -->
                    <div v-if="isOwnProfile && workouts.length > 0" class="mt-4">
                        <button 
                            @click="$inertia.visit(route('workouts.create'))"
                            class="w-full py-4 bg-gray-50 hover:bg-white border-2 border-dashed border-gray-300 hover:border-indigo-400 text-gray-500 hover:text-indigo-600 rounded-2xl font-bold transition-all flex items-center justify-center gap-2 group"
                        >
                            <span class="w-8 h-8 rounded-full bg-gray-200 group-hover:bg-indigo-100 text-gray-500 group-hover:text-indigo-600 flex items-center justify-center transition-colors">
                                +
                            </span>
                            Crear Nueva Rutina
                        </button>
                    </div>
                </div>

                <!-- Posts Tab -->
                <div v-else-if="activeContentTab === 'publicaciones'">
                    <div v-if="posts && posts.length > 0" class="space-y-4">
                        <PostCard 
                            v-for="post in posts" 
                            :key="post.id" 
                            :post="post" 
                        />
                    </div>
                    
                    <!-- Empty State for Posts -->
                    <div v-else class="text-center py-12 bg-white rounded-2xl border-2 border-dashed border-gray-200">
                        <div class="w-16 h-16 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                            📝
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Aún no hay publicaciones</h3>
                        <p class="text-gray-500 mb-6 max-w-xs mx-auto">Comparte tu primer post para que tu perfil cobre vida</p>
                        
                        <button 
                            v-if="isOwnProfile"
                            @click="$inertia.visit(route('posts.create'))"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-full font-bold hover:bg-indigo-700 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                        >
                            Crear Publicación
                        </button>
                    </div>
                </div>
            </div>
            
            <div v-else class="max-w-2xl mx-auto mt-8 text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100 px-6">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Este perfil es privado</h3>
                <p class="text-gray-500">Conecta con {{ profileUser.name }} para ver sus publicaciones y actividad reciente.</p>
                <button v-if="connection_status === 'none'" @click="connect" :disabled="processingConnection" class="mt-6 btn-primary-gradient">
                    Conectar ahora
                </button>
            </div>

        </div>

        <!-- Modal de Conexiones -->
        <ConnectionsModal 
            v-if="showConnectionsModal" 
            :connections="connections_list || []" 
            :isOwnProfile="isOwnProfile"
            @close="showConnectionsModal = false" 
        />

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

    </AuthenticatedLayout>
</template>

<style scoped>
.btn-primary { @apply inline-flex items-center justify-center px-4 py-3 bg-indigo-600 border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 transition disabled:opacity-50; }
.btn-primary-gradient {
    @apply inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:from-indigo-700 hover:to-purple-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:-translate-y-0.5;
}
.btn-secondary { @apply inline-flex items-center justify-center px-4 py-3 bg-white border border-gray-300 rounded-xl font-bold text-sm text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition disabled:opacity-50; }
.btn-secondary-gradient {
    @apply inline-flex items-center px-6 py-2.5 bg-white border border-gray-300 rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition ease-in-out duration-150 hover:shadow-md transform hover:-translate-y-0.5;
}
.btn-danger-gradient {
    @apply inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-red-500 to-pink-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:from-red-600 hover:to-pink-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:-translate-y-0.5;
}
.btn-disabled {
    @apply inline-flex items-center px-6 py-2.5 bg-gray-300 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed;
}
</style>