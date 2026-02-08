<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link, Head, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import ToastContainer from '@/Components/ToastContainer.vue';
import { useToast } from '@/composables/useToast';
import { useAutoPrefetch } from '@/composables/usePrefetch';
import axios from 'axios';

// Activar auto-prefetch de rutas importantes
useAutoPrefetch();

const page = usePage();
const authUser = computed(() => page.props.auth.user);
const pendingRequestsCount = computed(() => page.props.pendingRequestsCount);

const { success, error } = useToast();

const profilePictureUrl = computed(() => {
    const user = authUser.value;
    if (!user) return '';
    
    if (user.profile_picture_url) {
        if (user.profile_picture_url.startsWith('http')) {
            return user.profile_picture_url;
        }
        return `/storage/${user.profile_picture_url}`;
    }
    
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random`;
});

// Listen for flash messages from backend
watch(() => page.props, (newProps) => {
    if (newProps.flash?.success_toast) {
        success(newProps.flash.success_toast);
    }
    if (newProps.flash?.error_toast) {
        error(newProps.flash.error_toast);
    }
}, { deep: true });

const showMobileMenu = ref(false);

const unreadMessagesCount = ref(page.props.unreadMessagesCount || 0);

// Polling for unread messages
const fetchUnreadMessagesCount = async () => {
    try {
        const response = await axios.get(route('chat.unread-count'));
        unreadMessagesCount.value = response.data.count;
    } catch (err) {
        console.error('Error fetching unread messages count:', err);
    }
};

let messagePollingInterval = null;

onMounted(() => {
    messagePollingInterval = setInterval(fetchUnreadMessagesCount, 30000); // Cada 30 segundos
});

watch(() => page.props.unreadMessagesCount, (newVal) => {
    unreadMessagesCount.value = newVal || 0;
});

</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-200">
        <Head :title="page.props.title || 'GymPal'" />

        <nav v-if="authUser" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 sticky top-0 z-40 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Left: Brand Name & Nav -->
                    <div class="flex items-center gap-8">
                        <Link :href="route('feed.index')" class="text-2xl font-black tracking-tighter bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-violet-600 dark:from-indigo-400 dark:to-violet-400 hover:opacity-80 transition-opacity">
                            GymPal
                        </Link>
                        
                        <!-- Desktop Navigation Links -->
                        <div class="hidden sm:flex space-x-8">
                             <Link 
                                :href="route('feed.index')" 
                                class="nav-link" 
                                :class="{ 'active': route().current('feed.index') }"
                                prefetch
                            >
                                Feed
                            </Link>
                            <Link 
                                :href="route('discover.index')" 
                                class="nav-link" 
                                :class="{ 'active': route().current('discover.index') }"
                                prefetch
                            >
                                Descubrir
                            </Link>
                             <Link 
                                :href="route('connections.index')" 
                                class="nav-link relative" 
                                :class="{ 'active': route().current('connections.index') }"
                                prefetch
                            >
                                <span>Conexiones</span>
                                <span v-if="pendingRequestsCount > 0" class="notification-dot-desktop"></span>
                            </Link>
                        </div>
                    </div>

                    <!-- Right: Menu & Actions -->
                    <div class="flex flex-1 items-center justify-end">
                    <div class="hidden sm:flex sm:items-center sm:ml-6 space-x-4">
                        <!-- Chat -->
                        <Link :href="route('chat.index')" title="Chat" class="relative text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 p-2 rounded-full transition-colors">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                            <span v-if="unreadMessagesCount > 0" class="absolute top-0 right-0 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white border-2 border-white dark:border-gray-800 shadow-sm">
                                {{ unreadMessagesCount > 9 ? '9+' : unreadMessagesCount }}
                            </span>
                        </Link>

                        <!-- Notificaciones - SIEMPRE VISIBLE PARA DEBUG -->
                        <div class="flex items-center">
                            <NotificationBell />
                        </div>
                        
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center text-sm font-medium text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white focus:outline-none transition">
                                    <img :src="profilePictureUrl" class="h-8 w-8 rounded-full object-cover mr-2" :alt="authUser.name">
                                    <span>{{ authUser.name }}</span>
                                    <svg class="ml-2 -mr-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.show.public', { user: authUser.username })">Mi Perfil</DropdownLink>
                                <DropdownLink :href="route('profile.edit')">Editar Perfil</DropdownLink>
                                <DropdownLink :href="route('stats.index')">📊 Estadísticas</DropdownLink>
                                <DropdownLink :href="route('workouts.my-workouts')">📚 Mis Rutinas</DropdownLink>
                                <DropdownLink :href="route('workout-logs.index')">📋 Historial de Entrenamientos</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">Cerrar Sesión</DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                    
                    <div class="flex items-center sm:hidden space-x-3 -mr-2">
                        <!-- Notificaciones Móvil -->
                        <NotificationBell />
                        
                        <Link :href="route('chat.index')" title="Chat" class="relative text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 p-1 rounded-full transition-colors">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                            <span v-if="unreadMessagesCount > 0" class="absolute top-0 right-0 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white border-2 border-white dark:border-gray-800 shadow-sm leading-none">
                                {{ unreadMessagesCount > 9 ? '9+' : unreadMessagesCount }}
                            </span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow">
        <slot />
    </main>

    <div v-if="authUser" class="pb-16 md:pb-0"></div>
    
    <!-- Floating Menu (Mobile) -->
    <div v-if="showMobileMenu" @click="showMobileMenu = false" class="fixed inset-0 bg-black/50 z-40 md:hidden"></div>
    <div v-if="showMobileMenu" class="fixed bottom-20 left-1/2 -translate-x-1/2 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-4 z-50 md:hidden w-80 transition-colors">
        <div class="grid grid-cols-3 gap-3">
            <Link :href="route('posts.create')" class="flex flex-col items-center gap-2 p-3 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white hover:from-indigo-600 hover:to-purple-700 transition">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                <span class="text-xs font-bold">Post</span>
            </Link>
            <Link :href="route('workouts.create')" class="flex flex-col items-center gap-2 p-3 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white hover:from-green-600 hover:to-emerald-700 transition">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span class="text-xs font-bold">Rutina</span>
            </Link>
            <Link :href="route('connections.index')" class="flex flex-col items-center gap-2 p-3 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 text-white hover:from-blue-600 hover:to-cyan-700 transition relative">
                <span v-if="pendingRequestsCount > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center">{{ pendingRequestsCount }}</span>
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197M15 11a4 4 0 110-5.292M12 4.354a4 4 0 010 5.292" /></svg>
                <span class="text-xs font-bold">Conexiones</span>
            </Link>
            <Link :href="route('workouts.saved')" class="flex flex-col items-center gap-2 p-3 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" /></svg>
                <span class="text-xs font-bold">Guardadas</span>
            </Link>
            <Link :href="route('workout-logs.index')" class="flex flex-col items-center gap-2 p-3 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                <span class="text-xs font-bold">Historial</span>
            </Link>
            <Link :href="route('progress.index')" class="flex flex-col items-center gap-2 p-3 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                <span class="text-xs font-bold">Progreso</span>
            </Link>
        </div>
    </div>
    
    <nav v-if="authUser" class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-lg md:hidden z-30 transition-colors">
        <div class="flex items-center justify-around h-16">
            <Link :href="route('feed.index')" title="Inicio" class="bottom-nav-link" :class="{'active': route().current('feed.index')}">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                <span class="text-xs mt-1">Inicio</span>
            </Link>
            <Link :href="route('discover.index')" title="Descubrir" class="bottom-nav-link" :class="{'active': route().current('discover.index')}">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <span class="text-xs mt-1">Descubrir</span>
            </Link>
            <button @click="showMobileMenu = !showMobileMenu" title="Menú" class="bottom-nav-link -mt-1">
                <span class="inline-block p-3 bg-indigo-600 rounded-full shadow-md"><svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg></span>
            </button>
            <Link :href="route('workouts.my-workouts')" title="Mis Rutinas" class="bottom-nav-link" :class="{'active': route().current('workouts.my-workouts')}">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                <span class="text-xs mt-1">Rutinas</span>
            </Link>
            <Link :href="route('profile.show.public', { user: authUser.username })" title="Perfil" class="bottom-nav-link" :class="{'active': route().current('profile.show.public')}">
                <img :src="profilePictureUrl" alt="Perfil" class="h-6 w-6 rounded-full object-cover">
                <span class="text-xs mt-1">Perfil</span>
            </Link>
        </div>
    </nav>

    <!-- Toast Notifications Container -->
    <ToastContainer />
    </div>
</template>

<style scoped>
.nav-link { @apply inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition; }
.nav-link.active { @apply border-indigo-400 text-gray-900 focus:border-indigo-700; }
.sidebar-link { @apply block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition; }
.sidebar-link.active { @apply border-indigo-400 text-indigo-700 bg-indigo-50; }
.bottom-nav-link { @apply flex-grow flex flex-col items-center justify-center text-center px-1 text-gray-500 hover:text-indigo-600 transition-colors; }
.bottom-nav-link.active { @apply text-indigo-600; }
.notification-dot-desktop { @apply absolute top-1/2 right-0 block h-2 w-2 -mt-2 -mr-1 transform translate-x-1/2 -translate-y-1/2 rounded-full bg-red-500 ring-2 ring-white; }
.notification-dot-mobile { @apply absolute top-1 right-1/2 mr-[-20px] block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white; }
.notification-badge { @apply px-2 py-0.5 bg-indigo-600 text-white text-xs font-semibold rounded-full; }
</style>