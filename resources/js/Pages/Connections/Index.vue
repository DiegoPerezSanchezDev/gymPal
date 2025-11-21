<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConnectionSkeleton from '@/Components/Skeletons/ConnectionSkeleton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    pendingRequests: Array,
    gymPals: Array,
});

// Guardará el ID de la solicitud que se está procesando.
const processingId = ref(null);
const isLoading = ref(true);

onMounted(() => {
    // Simulamos una carga rápida si los datos ya están presentes, 
    // o podríamos usar un prop desde el backend si implementamos carga diferida real.
    // Por ahora, para efecto visual, lo dejamos brevemente.
    if (props.pendingRequests && props.gymPals) {
        setTimeout(() => isLoading.value = false, 500);
    }
});

function acceptRequest(connectionId) {
    processingId.value = connectionId;
    router.patch(route('connections.accept', connectionId), {}, {
        preserveScroll: true,
        onFinish: () => {
            processingId.value = null; 
        }
    });
}

function rejectRequest(connectionId) {
    processingId.value = connectionId;
    router.patch(route('connections.reject', connectionId), {}, {
        preserveScroll: true,
        onFinish: () => {
            processingId.value = null; 
        }
    });
}

function disconnectUser(connectionId) {
    if (confirm('¿Estás seguro de que quieres eliminar esta conexión?')) {
        router.delete(route('connections.destroy', connectionId), {
            preserveScroll: true,
            onStart: () => {
                processingId.value = connectionId;
            },
            onFinish: () => {
                processingId.value = null;
            },
        });
    }
}
</script>

<template>
    <Head title="Mis Conexiones" />

    <AuthenticatedLayout>
        <!-- Cabecera de la página -->
        <div class="flex flex-col items-center justify-center pt-8 mb-8">
            <h2 class="text-3xl font-extrabold text-indigo-700 tracking-tight">Mis Conexiones</h2>
            <p class="text-lg text-gray-600 max-w-xl text-center">Gestiona tus solicitudes pendientes y tus GymPals.</p>
        </div>

        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 pb-12 space-y-8">

            <!-- Loading State -->
            <div v-if="isLoading">
                <ConnectionSkeleton :count="2" />
                <ConnectionSkeleton :count="4" />
            </div>

            <div v-else class="space-y-12">
                <!-- SECCIÓN DE SOLICITUDES PENDIENTES -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                            Solicitudes Pendientes
                            <span v-if="pendingRequests.length > 0" class="ml-3 px-2.5 py-0.5 bg-indigo-100 text-indigo-700 text-xs font-bold rounded-full shadow-sm">
                                {{ pendingRequests.length }}
                            </span>
                        </h3>

                        <div v-if="pendingRequests.length === 0" class="text-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                            <p class="text-gray-500 font-medium">No tienes ninguna solicitud pendiente.</p>
                        </div>

                        <div v-else class="grid gap-4">
                            <div v-for="request in pendingRequests" :key="request.id"
                                class="flex flex-col sm:flex-row items-center justify-between p-4 bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-md transition-all duration-200">
                                
                                <!-- Info del Usuario -->
                                <Link :href="route('profile.show.public', { user: request.sender.username })" class="flex items-center gap-4 w-full sm:w-auto mb-4 sm:mb-0 group">
                                    <div class="relative">
                                        <img :src="request.sender.profile_picture_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(request.sender.name) + '&background=random&color=fff'"
                                            :alt="request.sender.name"
                                            class="w-14 h-14 rounded-full object-cover border-2 border-white shadow-sm group-hover:border-indigo-100 transition-colors">
                                    </div>
                                    <div class="min-w-0 text-left">
                                        <h4 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors text-lg">{{ request.sender.name }}</h4>
                                        <p class="text-sm text-gray-500">@{{ request.sender.username }}</p>
                                        <div v-if="request.sender.common_interests_count > 0" class="mt-1 text-xs text-indigo-500 font-medium">
                                            {{ request.sender.common_interests_count }} intereses en común
                                        </div>
                                    </div>
                                </Link>

                                <!-- Contenedor de botones -->
                                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                                    <button @click="rejectRequest(request.id)" 
                                            :disabled="processingId === request.id"
                                            class="px-4 py-2 text-sm font-medium rounded-lg text-gray-600 bg-gray-100 hover:bg-red-50 hover:text-red-600 hover:border-red-200 border border-transparent transition-all disabled:opacity-50">
                                        Rechazar
                                    </button>
                                    <button @click="acceptRequest(request.id)" 
                                            :disabled="processingId === request.id"
                                            class="inline-flex items-center justify-center min-w-[100px] px-4 py-2 text-sm font-bold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:transform-none disabled:shadow-none">
                                        <svg v-if="processingId === request.id" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span v-else>Aceptar</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN DE MIS GYMPALS -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                            Mis GymPals
                            <span v-if="gymPals.length > 0" class="ml-3 px-2.5 py-0.5 bg-green-100 text-green-700 text-xs font-bold rounded-full shadow-sm">
                                {{ gymPals.length }}
                            </span>
                        </h3>

                        <div v-if="gymPals.length === 0" class="text-center py-12">
                            <div class="mx-auto h-24 w-24 bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                                <svg class="h-12 w-12 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m13-6.13a4 4 0 11-8 0 4 4 0 018 0zM5 8a4 4 0 108 0 4 4 0 00-8 0z"></path></svg>
                            </div>
                            <h4 class="text-lg font-medium text-gray-900">Aún no has conectado con nadie</h4>
                            <p class="text-gray-500 mt-1 mb-6 max-w-sm mx-auto">¡Explora la comunidad y encuentra personas con tus mismos intereses!</p>
                            <Link :href="route('discover.index')" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:scale-105">
                                Encontrar GymPals
                            </Link>
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="pal in gymPals" :key="pal.id"
                                class="flex items-center justify-between p-4 bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-200 group">
                                
                                <!-- Info del Usuario -->
                                <Link :href="route('profile.show.public', { user: pal.username })" class="flex items-center gap-3 min-w-0">
                                    <div class="relative">
                                        <img :src="pal.profile_picture_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(pal.name) + '&background=random&color=fff'"
                                            :alt="pal.name"
                                            class="w-12 h-12 rounded-full object-cover border-2 border-gray-100 group-hover:border-indigo-200 transition-colors">
                                        <span class="absolute bottom-0 right-0 block h-3 w-3 rounded-full ring-2 ring-white bg-green-400"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-semibold text-gray-900 truncate group-hover:text-indigo-600 transition-colors">{{ pal.name }}</h4>
                                        <p class="text-xs text-gray-500 truncate">@{{ pal.username }}</p>
                                    </div>
                                </Link>

                                <!-- Contenedor de botones (Derecha) -->
                                <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                                    <Link :href="route('chat.show', { user: pal.username })" 
                                        class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-full transition-colors"
                                        title="Enviar mensaje">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    </Link>
                                    <button @click="disconnectUser(pal.connection_id)" 
                                            :disabled="processingId === pal.connection_id"
                                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-full transition-colors"
                                            title="Desconectar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>