<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    pendingRequests: Array,
    gymPals: Array,
});

// Guardará el ID de la solicitud que se está procesando.
const processingId = ref(null);

function acceptRequest(connectionId) {
    processingRequestId.value = connectionId;
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

        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 pb-12 space-y-12">

            <!-- SECCIÓN DE SOLICITUDES PENDIENTES -->
            <div class="bg-white overflow-hidden shadow-md rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-3">
                        Solicitudes Pendientes
                        <span class="ml-2 px-2.5 py-1 bg-indigo-100 text-indigo-800 text-xs font-semibold rounded-full">
                            {{ pendingRequests.length }}
                        </span>
                    </h3>

                    <div v-if="pendingRequests.length === 0" class="text-center py-8">
                        <p class="text-gray-500">No tienes ninguna solicitud pendiente.</p>
                    </div>

                    <ul v-else class="space-y-4">
                        <li v-for="request in pendingRequests" :key="request.id"
                            class="flex items-center justify-between p-3 rounded-md hover:bg-gray-50 transition-colors">
                            
                            <!-- Info del Usuario -->
                            <Link :href="route('profile.show.public', { user: request.sender.username })" class="flex items-center gap-4 min-w-0">
                                <img :src="request.sender.profile_picture_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(request.sender.name) + '&background=random&color=fff'"
                                    :alt="request.sender.name"
                                    class="w-12 h-12 rounded-full object-cover border-2 border-gray-200 flex-shrink-0">
                                <div class="min-w-0">
                                    <h4 class="font-semibold text-gray-800 truncate">{{ request.sender.name }}</h4>
                                    <p class="text-sm text-gray-500 truncate">@{{ request.sender.username }}</p>
                                </div>
                            </Link>

                            <!-- Contenedor de botones -->
                            <div class="flex items-center gap-2 flex-shrink-0 ml-4">
                                <button @click="rejectRequest(request.id)" 
                                        :disabled="processingRequestId === request.id"
                                        class="px-3 py-1.5 text-xs font-medium rounded-md text-gray-700 bg-gray-200 hover:bg-red-500 hover:text-white transition disabled:opacity-50 disabled:cursor-wait">
                                    Rechazar
                                </button>
                                <button @click="acceptRequest(request.id)" 
                                        :disabled="processingRequestId === request.id"
                                        class="inline-flex items-center justify-center w-[70px] px-3 py-1.5 text-xs font-medium rounded-md text-white bg-gradient-to-r from-green-400 to-pink-500 hover:opacity-90 transition-opacity disabled:opacity-50 disabled:cursor-wait">
                                    <span v-if="processingRequestId === request.id">...</span>
                                    <span v-else>Aceptar</span>
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- SECCIÓN DE MIS GYMPALS -->
            <div class="bg-white overflow-hidden shadow-md rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-3">
                        Mis GymPals
                        <span class="ml-2 px-2.5 py-1 bg-indigo-100 text-indigo-800 text-xs font-semibold rounded-full">
                            {{ gymPals.length }}
                        </span>
                    </h3>

                    <div v-if="gymPals.length === 0" class="text-center py-8">
                        <p class="text-gray-500">Aún no has conectado con nadie.</p>
                        <Link :href="route('discover.index')" class="text-sm text-indigo-600 hover:underline font-semibold mt-1 inline-block">
                            ¡Encuentra nuevos GymPals aquí!
                        </Link>
                    </div>

                    <ul v-else class="space-y-4">
                        <li v-for="pal in gymPals" :key="pal.id"
                            class="flex items-center justify-between p-3 rounded-md hover:bg-gray-50 transition-colors">
                            
                            <!-- Info del Usuario -->
                            <Link :href="route('profile.show.public', { user: pal.username })" class="flex items-center gap-4 min-w-0">
                                <img :src="pal.profile_picture_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(pal.name) + '&background=random&color=fff'"
                                    :alt="pal.name"
                                    class="w-12 h-12 rounded-full object-cover border-2 border-gray-200 flex-shrink-0">
                                <div class="min-w-0">
                                    <h4 class="font-semibold text-gray-800 truncate">{{ pal.name }}</h4>
                                    <p class="text-sm text-gray-500 truncate">@{{ pal.username }}</p>
                                </div>
                            </Link>

                            <!-- Contenedor de botones (Derecha) -->
                            <div class="flex items-center gap-2 flex-shrink-0 ml-4">
                                <button @click="disconnectUser(pal.connection_id)" 
                                        :disabled="processingId === pal.connection_id"
                                        class="px-3 py-1.5 text-xs font-medium rounded-md text-gray-700 bg-gray-200 hover:bg-gray-300 transition disabled:opacity-50">
                                    Desconectar
                                </button>
                                <Link :href="route('chat.show', { user: pal.username })" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-gradient-to-r from-pink-500 to-orange-500 hover:brightness-110 shadow-sm transition-all">
                                    Chatear
                                </Link>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>