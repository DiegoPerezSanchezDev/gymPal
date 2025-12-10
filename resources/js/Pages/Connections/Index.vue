<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    gymPals: Array,
    pendingSent: Array,
    pendingReceived: Array,
    currentTab: {
        type: String,
        default: 'gymPals',
    },
    counts: {
        type: Object,
        default: () => ({ gymPals: 0, pendingSent: 0, pendingReceived: 0 }),
    },
});

const activeTab = ref(props.currentTab);
const processingId = ref(null);

const switchTab = (tab) => {
    activeTab.value = tab;
};

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

const currentList = computed(() => {
    if (activeTab.value === 'pendingSent') return props.pendingSent;
    if (activeTab.value === 'pendingReceived') return props.pendingReceived;
    return props.gymPals;
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
</script>

<template>
    <Head title="Conexiones" />
    
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-4 sm:py-8 transition-colors">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight transition-colors">
                        👥 Mis Conexiones
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm transition-colors">
                        Gestiona tu red de GymPals
                    </p>
                </div>

                <!-- Tabs -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6 transition-colors">
                    <div class="grid grid-cols-3">
                        <button
                            @click="switchTab('gymPals')"
                            class="px-3 sm:px-6 py-3 sm:py-4 font-bold text-xs sm:text-sm transition-all relative"
                            :class="activeTab === 'gymPals' 
                                ? 'bg-indigo-600 text-white' 
                                : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'"
                        >
                            <span class="hidden sm:inline">💪 GymPals</span>
                            <span class="sm:hidden">💪</span>
                            <span class="ml-1">({{ counts.gymPals }})</span>
                        </button>
                        <button
                            @click="switchTab('pendingReceived')"
                            class="px-3 sm:px-6 py-3 sm:py-4 font-bold text-xs sm:text-sm transition-all relative"
                            :class="activeTab === 'pendingReceived' 
                                ? 'bg-indigo-600 text-white' 
                                : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'"
                        >
                            <span class="hidden sm:inline">📥 Solicitudes</span>
                            <span class="sm:hidden">📥</span>
                            <span class="ml-1">({{ counts.pendingReceived }})</span>
                            <span v-if="counts.pendingReceived > 0" class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                        </button>
                        <button
                            @click="switchTab('pendingSent')"
                            class="px-3 sm:px-6 py-3 sm:py-4 font-bold text-xs sm:text-sm transition-all"
                            :class="activeTab === 'pendingSent' 
                                ? 'bg-indigo-600 text-white' 
                                : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'"
                        >
                            <span class="hidden sm:inline">📤 Enviadas</span>
                            <span class="sm:hidden">📤</span>
                            <span class="ml-1">({{ counts.pendingSent }})</span>
                        </button>
                    </div>
                </div>

                <!-- GymPals Tab -->
                <div v-if="activeTab === 'gymPals'">
                    <div v-if="gymPals.length > 0" class="space-y-3">
                        <Link
                            v-for="user in gymPals"
                            :key="user.id"
                            :href="route('profile.show.public', user.username)"
                            class="block bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-600 transition-all"
                        >
                            <div class="flex items-center gap-4">
                                <img
                                    :src="getAvatarUrl(user)"
                                    :alt="user.name"
                                    class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover border-2 border-gray-100 dark:border-gray-700 flex-shrink-0"
                                />
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-gray-900 dark:text-white text-base sm:text-lg truncate">{{ user.name }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">@{{ user.username }}</p>
                                    <span
                                        v-if="user.experience_level"
                                        class="inline-block mt-1 text-xs px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-semibold"
                                    >
                                        {{ user.experience_level }}
                                    </span>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </Link>
                    </div>
                    <div v-else class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border-2 border-dashed border-gray-200 dark:border-gray-700 p-8 sm:p-12 text-center">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl sm:text-4xl">
                            💪
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-2">No tienes GymPals aún</h3>
                        <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
                            Conecta con otros usuarios para formar tu equipo de entrenamiento
                        </p>
                        <Link
                            :href="route('discover.index')"
                            class="inline-flex items-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg text-sm sm:text-base"
                        >
                            <span>🔍</span>
                            Descubrir GymPals
                        </Link>
                    </div>
                </div>

                <!-- Solicitudes Recibidas Tab -->
                <div v-if="activeTab === 'pendingReceived'">
                    <div v-if="pendingReceived.length > 0" class="space-y-3">
                        <div
                            v-for="user in pendingReceived"
                            :key="user.id"
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 transition-all overflow-hidden"
                        >
                            <Link 
                                :href="route('profile.show.public', user.username)" 
                                class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                            >
                                <div class="flex items-center gap-3 sm:gap-4">
                                    <img
                                        :src="getAvatarUrl(user)"
                                        :alt="user.name"
                                        class="w-12 h-12 sm:w-14 sm:h-14 rounded-full object-cover border-2 border-gray-100 dark:border-gray-700 flex-shrink-0"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-bold text-gray-900 dark:text-white text-base sm:text-lg truncate">{{ user.name }}</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">@{{ user.username }}</p>
                                        <span
                                            v-if="user.experience_level"
                                            class="inline-block mt-1 text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold"
                                        >
                                            {{ user.experience_level }}
                                        </span>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </Link>
                            <div class="px-4 pb-4 flex gap-2">
                                <button
                                    @click.stop="acceptRequest(user.connection_id)"
                                    :disabled="processingId === user.connection_id"
                                    class="btn-primary-gradient flex-1 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span class="hidden sm:inline">✓ Aceptar</span>
                                    <span class="sm:hidden">✓ Aceptar</span>
                                </button>
                                <button
                                    @click.stop="rejectRequest(user.connection_id)"
                                    :disabled="processingId === user.connection_id"
                                    class="btn-danger-gradient flex-1 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span class="hidden sm:inline">✗ Rechazar</span>
                                    <span class="sm:hidden">✗ Rechazar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border-2 border-dashed border-gray-200 dark:border-gray-700 p-8 sm:p-12 text-center">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl sm:text-4xl">
                            📥
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-2">No tienes solicitudes pendientes</h3>
                        <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400">Las solicitudes de conexión aparecerán aquí</p>
                    </div>
                </div>

                <!-- Solicitudes Enviadas Tab -->
                <div v-if="activeTab === 'pendingSent'">
                    <div v-if="pendingSent.length > 0" class="space-y-3">
                        <Link
                            v-for="user in pendingSent"
                            :key="user.id"
                            :href="route('profile.show.public', user.username)"
                            class="block bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-all opacity-75"
                        >
                            <div class="flex items-center gap-4">
                                <img
                                    :src="getAvatarUrl(user)"
                                    :alt="user.name"
                                    class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover border-2 border-gray-100 dark:border-gray-700 flex-shrink-0"
                                />
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-gray-900 dark:text-white text-base sm:text-lg truncate">{{ user.name }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">@{{ user.username }}</p>
                                    <span class="inline-block mt-1 text-xs px-2 py-0.5 rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 font-semibold">
                                        ⏳ Pendiente
                                    </span>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </Link>
                    </div>
                    <div v-else class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border-2 border-dashed border-gray-200 dark:border-gray-700 p-8 sm:p-12 text-center">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl sm:text-4xl">
                            📤
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-2">No has enviado solicitudes</h3>
                        <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400 mb-6">Descubre usuarios y envía solicitudes de conexión</p>
                        <Link
                            :href="route('discover.index')"
                            class="inline-flex items-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg text-sm sm:text-base"
                        >
                            <span>🔍</span>
                            Descubrir GymPals
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.btn-primary-gradient {
    @apply inline-flex items-center justify-center px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:from-indigo-700 hover:to-purple-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:-translate-y-0.5;
}

.btn-danger-gradient {
    @apply inline-flex items-center justify-center px-6 py-2.5 bg-gradient-to-r from-red-500 to-pink-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:from-red-600 hover:to-pink-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:-translate-y-0.5;
}
</style>