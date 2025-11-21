// resources/js/Pages/Profile/ShowPublic.vue
<script setup>
// Importaciones y props
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProfileSkeleton from '@/Components/Skeletons/ProfileSkeleton.vue';
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
});

const processingConnection = ref(false);
const isLoading = ref(true);

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
const reject = () => {
    if (processingConnection.value || !confirm('¿Estás seguro de que quieres rechazar esta solicitud?')) return;
    router.patch(route('connections.reject', props.connection_id), {}, {
        preserveScroll: true,
        onStart: () => processingConnection.value = true,
        onFinish: () => processingConnection.value = false,
    });
};
const disconnect = () => {
    if (processingConnection.value || !confirm('¿Estás seguro de que quieres desconectar de este usuario?')) return;
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
</script>

<template>
    <Head :title="title" />
    <AuthenticatedLayout>
        
        <div v-if="isLoading">
            <ProfileSkeleton />
        </div>

        <div v-else-if="profileUser" class="container mx-auto px-2 sm:px-4 py-8">
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
                        <!-- Nivel de Experiencia (Badge con gradiente) -->
                        <div v-if="profileUser.experience_level" class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 bg-gradient-to-r from-green-400 to-emerald-600 text-white text-xs font-bold uppercase tracking-wide px-3 py-1 rounded-full shadow-md">
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
                        <div class="text-center">
                            <span class="block text-2xl font-bold text-gray-800">{{ connections_count ?? 0 }}</span>
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Conexiones</span>
                        </div>
                        <div class="text-center border-l border-gray-100 pl-8">
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
                    <div class="w-full max-w-sm mx-auto space-y-3">
                        <Link v-if="isOwnProfile" :href="route('profile.edit')" class="btn-primary w-full shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all">
                            Editar Perfil
                        </Link>

                        <div v-else class="grid grid-cols-1 gap-3">
                            <div class="w-full">
                                <button v-if="connection_status === 'none'" @click="connect" :disabled="processingConnection" class="btn-primary-gradient w-full shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all flex justify-center items-center gap-2">
                                    <svg v-if="processingConnection" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span>Conectar</span>
                                </button>
                                <button v-if="connection_status === 'sent'" disabled class="btn-disabled w-full flex justify-center items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Solicitud Enviada
                                </button>
                                <div v-if="connection_status === 'accepted'" class="grid grid-cols-2 gap-3">
                                    <button @click="disconnect" :disabled="processingConnection" class="btn-secondary w-full text-red-600 hover:bg-red-50 border-red-200">Desconectar</button>
                                    <button @click="sendMessage" class="btn-primary w-full bg-indigo-600 hover:bg-indigo-700">Mensaje</button>
                                </div>
                                
                                <div v-if="connection_status === 'received'" class="grid grid-cols-2 gap-3">
                                    <button @click="reject" :disabled="processingConnection" class="btn-secondary w-full hover:text-red-600">Rechazar</button>
                                    <button @click="accept" :disabled="processingConnection" class="btn-primary w-full bg-green-600 hover:bg-green-700 border-transparent">Aceptar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="profileUser.created_at" class="mt-8 text-xs text-gray-400 text-center">
                        Miembro desde {{ new Date(profileUser.created_at).toLocaleDateString('es-ES', { year: 'numeric', month: 'long' }) }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
<style scoped>
.btn-primary { @apply inline-flex items-center justify-center px-4 py-3 bg-indigo-600 border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 transition disabled:opacity-50; }
.btn-primary-gradient { @apply inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-bold rounded-xl shadow-sm text-white bg-gradient-to-r from-green-400 to-indigo-500 hover:opacity-90 transition disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center justify-center px-4 py-3 bg-white border border-gray-300 rounded-xl font-bold text-sm text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition disabled:opacity-50; }
.btn-secondary-gradient { @apply inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-bold rounded-xl shadow-sm text-white bg-gradient-to-r from-red-400 to-pink-500 hover:opacity-90 transition disabled:opacity-50; }
.btn-danger-gradient { @apply inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-bold rounded-xl shadow-sm text-white bg-gradient-to-r from-red-500 to-rose-600 hover:opacity-90 transition disabled:opacity-50; }
.btn-disabled { @apply inline-flex items-center justify-center px-4 py-3 bg-gray-200 border border-transparent rounded-xl font-bold text-sm text-gray-400 uppercase tracking-widest cursor-not-allowed; }
</style>
</style>