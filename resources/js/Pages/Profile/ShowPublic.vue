// resources/js/Pages/Profile/ShowPublic.vue
<script setup>
// Importaciones y props
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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
    return props.profileUser.profile_picture_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(props.profileUser.name || 'G P')}&background=random&color=fff&size=160`;
});
const fitnessInterestsArray = computed(() => {
    return props.profileUser.fitness_interests || [];
});
const levelStyles = computed(() => {
    const level = props.profileUser.experience_level?.toLowerCase();
    switch (level) {
        case 'principiante': return { span: 'bg-fuchsia-100 text-fuchsia-800', svg: 'text-fuchsia-500' };
        case 'intermedio': return { span: 'bg-teal-100 text-teal-800', svg: 'text-teal-500' };
        case 'avanzado': return { span: 'bg-amber-100 text-amber-800', svg: 'text-amber-500' };
        default: return { span: 'bg-blue-100 text-blue-800', svg: 'text-gray-500' };
    }
});

</script>

<template>
    <Head :title="title" />
    <AuthenticatedLayout>
        <div v-if="profileUser" class="container mx-auto px-2 sm:px-4 py-8">
            <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-8 flex flex-col items-center">
                <img :src="avatarUrl" :alt="profileUser.name" class="w-32 h-32 rounded-full object-cover border-4 border-indigo-200 shadow mb-4">
                <h1 class="text-3xl font-bold text-gray-800 mb-1">{{ profileUser.display_name || profileUser.name }}</h1>
                <p v-if="profileUser.username" class="text-md text-indigo-600 mb-2">@{{ profileUser.username }}</p>
                <p v-if="profileUser.bio" class="text-gray-600 text-center mb-4 whitespace-pre-line">{{ profileUser.bio }}</p>

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
                    <div v-if="profileUser.experience_level" class="mb-2 flex flex-wrap gap-2 justify-center">
                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full" :class="levelStyles.span">
                            <svg class="w-4 h-4 mr-1" :class="levelStyles.svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            {{ profileUser.experience_level }}
                        </span>
                    </div>
                    <div v-if="fitnessInterestsArray.length" class="mb-4 flex flex-wrap gap-2 justify-center">
                        <span v-for="interest in fitnessInterestsArray" :key="interest.id" class="inline-block bg-indigo-100 text-indigo-800 text-xs font-semibold px-3 py-1 rounded-full">
                            {{ interest.name }}
                        </span>
                    </div>
                </div>

                <div class="flex gap-6 justify-center mb-4">
                    <div class="text-center">
                        <span class="block text-xl font-bold text-gray-700">{{ connections_count ?? 0 }}</span>
                        <span class="text-xs text-gray-500">Conexiones</span>
                    </div>
                    <div class="text-center">
                        <span class="block text-xl font-bold text-gray-700">{{ profileUser.posts_count ?? 0 }}</span>
                        <span class="text-xs text-gray-500">Publicaciones</span>
                    </div>
                </div>

                <div v-if="profileUser.created_at" class="mb-4 text-xs text-gray-400 text-center">
                    Miembro desde {{ new Date(profileUser.created_at).toLocaleDateString('es-ES', { year: 'numeric', month: 'long' }) }}
                </div>

                <div class="mt-4 w-full max-w-xs mx-auto">
                    <Link v-if="isOwnProfile" :href="route('profile.edit')" class="btn-primary w-full">
                        Editar Perfil
                    </Link>

                    <div v-else class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <button v-if="connection_status === 'none'" @click="connect" :disabled="processingConnection" class="btn-primary-gradient w-full">Conectar</button>
                            <button v-if="connection_status === 'sent'" disabled class="btn-disabled w-full">Solicitud Enviada</button>
                            <button v-if="connection_status === 'accepted'" @click="disconnect" :disabled="processingConnection" class="btn-secondary w-full">Conectado</button>
                            
                            <div v-if="connection_status === 'received'" class="grid grid-cols-2 gap-2">
                                <button @click="reject" :disabled="processingConnection" class="btn-secondary">Rechazar</button>
                                <button @click="accept" :disabled="processingConnection" class="btn-primary">Aceptar</button>
                            </div>
                        </div>

                        <template v-if="connection_status === 'accepted'">
                            <button @click="sendMessage" class="btn-secondary">Mensaje</button>
                            <button @click="() => alert('Compartir próximamente')" class="btn-secondary">Compartir</button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.btn-primary { @apply inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition disabled:opacity-50; }
.btn-primary-gradient { @apply inline-flex items-center justify-center px-4 py-2 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-green-400 to-indigo-500 hover:opacity-90 transition disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition disabled:opacity-50; }
.btn-disabled { @apply inline-flex items-center justify-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-400 uppercase tracking-widest cursor-not-allowed; }
</style>