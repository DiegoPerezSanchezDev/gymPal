<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConnectionSkeleton from '@/Components/Skeletons/ConnectionSkeleton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    pendingRequests: Array,
    gymPals: Array,
});

const processingId = ref(null);
const isLoading = ref(true);

onMounted(() => {
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
            }
        });
    }
}

// Helper para obtener avatar URL
const getAvatarUrl = (user) => {
    if (user.profile_picture_url) {
        if (user.profile_picture_url.startsWith('http')) {
            return user.profile_picture_url;
        }
        return `/storage/${user.profile_picture_url}`;
    }
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random&color=fff&size=128`;
};
</script>

<template>
    <Head title="Mis Conexiones" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-extrabold text-xl text-gray-900 leading-tight">
                        Mis Conexiones
                    </h2>
                    <p class="text-xs text-gray-500 font-medium">Gestiona tus GymPals</p>
                </div>
            </div>
        </template>

        <div class="py-6 md:py-12">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- Loading State -->
                <div v-if="isLoading">
                    <ConnectionSkeleton :count="2" />
                    <ConnectionSkeleton :count="4" />
                </div>

                <div v-else class="space-y-8">
                    <!-- SOLICITUDES PENDIENTES -->
                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 px-6 py-4 border-b border-gray-100">
                            <h3 class="text-lg font-extrabold text-gray-900 flex items-center gap-3">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                Solicitudes Pendientes
                                <span v-if="pendingRequests.length > 0" class="px-3 py-1 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold rounded-full shadow-md animate-pulse">
                                    {{ pendingRequests.length }}
                                </span>
                            </h3>
                        </div>

                        <div class="p-6">
                            <div v-if="pendingRequests.length === 0" class="text-center py-12">
                                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="text-gray-500 font-medium">No tienes solicitudes pendientes</p>
                            </div>

                            <div v-else class="space-y-3">
                                <div v-for="request in pendingRequests" :key="request.id"
                                    class="flex flex-col sm:flex-row items-center justify-between p-4 bg-gradient-to-r from-white to-indigo-50 border-2 border-indigo-100 rounded-2xl shadow-md hover:shadow-lg transition-all duration-300 group">
                                    
                                    <Link :href="route('profile.show.public', { user: request.sender.username })" class="flex items-center gap-4 w-full sm:w-auto mb-4 sm:mb-0">
                                        <div class="relative">
                                            <img :src="getAvatarUrl(request.sender)"
                                                :alt="request.sender.name"
                                                class="w-16 h-16 rounded-full object-cover border-4 border-white shadow-lg group-hover:border-indigo-200 transition-all">
                                            <span class="absolute -bottom-1 -right-1 w-6 h-6 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full border-2 border-white flex items-center justify-center">
                                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 text-left">
                                            <h4 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors text-lg">{{ request.sender.name }}</h4>
                                            <p class="text-sm text-gray-500">@{{ request.sender.username }}</p>
                                            <div v-if="request.sender.common_interests_count > 0" class="mt-1 flex items-center gap-1 text-xs text-indigo-600 font-bold">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                {{ request.sender.common_interests_count }} intereses en común
                                            </div>
                                        </div>
                                    </Link>

                                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                                        <button @click="rejectRequest(request.id)" 
                                                :disabled="processingId === request.id"
                                                class="px-5 py-2.5 text-sm font-bold rounded-full text-gray-600 bg-white hover:bg-red-50 hover:text-red-600 border-2 border-gray-200 hover:border-red-300 transition-all disabled:opacity-50 shadow-sm hover:shadow-md">
                                            Rechazar
                                        </button>
                                        <button @click="acceptRequest(request.id)" 
                                                :disabled="processingId === request.id"
                                                class="px-5 py-2.5 text-sm font-bold rounded-full text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md hover:shadow-lg transition-all disabled:opacity-50 transform hover:scale-105">
                                            Aceptar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MIS GYMPALS -->
                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-gray-100">
                            <h3 class="text-lg font-extrabold text-gray-900 flex items-center gap-3">
                                <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                Mis GymPals
                                <span class="px-3 py-1 bg-gradient-to-r from-purple-500 to-pink-500 text-white text-xs font-bold rounded-full shadow-md">
                                    {{ gymPals.length }}
                                </span>
                            </h3>
                        </div>

                        <div class="p-6">
                            <div v-if="gymPals.length === 0" class="text-center py-12">
                                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <p class="text-gray-500 font-medium mb-4">Aún no tienes GymPals</p>
                                <Link :href="route('discover.index')" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-full shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    Descubrir GymPals
                                </Link>
                            </div>

                            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-for="pal in gymPals" :key="pal.id"
                                    class="p-4 bg-white border-2 border-gray-100 rounded-2xl shadow-md hover:shadow-xl hover:border-purple-200 transition-all duration-300 group">
                                    
                                    <Link :href="route('profile.show.public', { user: pal.username })" class="flex items-center gap-4 mb-4">
                                        <img :src="getAvatarUrl(pal)"
                                            :alt="pal.name"
                                            class="w-14 h-14 rounded-full object-cover border-4 border-purple-100 group-hover:border-purple-300 transition-all shadow-sm">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-gray-900 group-hover:text-purple-600 transition-colors truncate">{{ pal.name }}</h4>
                                            <p class="text-sm text-gray-500 truncate">@{{ pal.username }}</p>
                                        </div>
                                    </Link>

                                    <div class="flex items-center gap-2">
                                        <Link :href="route('chat.show', { user: pal.username })" 
                                            class="flex-1 px-4 py-2 text-sm font-bold rounded-full text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md hover:shadow-lg transition-all text-center transform hover:scale-105">
                                            💬 Chat
                                        </Link>
                                        <button @click="disconnectUser(pal.connection_id)" 
                                                :disabled="processingId === pal.connection_id"
                                                class="px-4 py-2 text-sm font-bold rounded-full text-red-600 bg-red-50 hover:bg-red-100 border-2 border-red-100 hover:border-red-200 transition-all disabled:opacity-50 shadow-sm hover:shadow-md">
                                            Desconectar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>