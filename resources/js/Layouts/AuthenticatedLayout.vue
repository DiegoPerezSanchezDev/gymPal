<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link, Head, usePage } from '@inertiajs/vue3';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import ToastContainer from '@/Components/ToastContainer.vue';
import { useToast } from '@/composables/useToast';

const page = usePage();
const authUser = computed(() => page.props.auth.user);
const pendingRequestsCount = computed(() => page.props.pendingRequestsCount);

const { success, error } = useToast();

// Listen for flash messages from backend
watch(() => page.props, (newProps) => {
    if (newProps.flash?.success_toast) {
        success(newProps.flash.success_toast);
    }
    if (newProps.flash?.error_toast) {
        error(newProps.flash.error_toast);
    }
}, { deep: true });

</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <Head :title="page.props.title || 'GymPal'" />

        <nav v-if="authUser" class="bg-white border-b border-gray-100 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="shrink-0 flex items-center">
                        <Link :href="route('feed.index')">
                            <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
                        </Link>
                    </div>
                    <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                        <Link :href="route('feed.index')" class="nav-link" :class="{ 'active': route().current('feed.index') }">Feed</Link>
                        <Link :href="route('discover.index')" class="nav-link" :class="{ 'active': route().current('discover.index') }">Descubrir</Link>
                        <Link :href="route('connections.index')" class="nav-link relative" :class="{ 'active': route().current('connections.index') }">
                            <span>Conexiones</span>
                            <span v-if="pendingRequestsCount > 0" class="notification-dot-desktop"></span>
                        </Link>
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="hidden sm:flex sm:items-center sm:ml-6 space-x-4">
                        <!-- Chat -->
                        <Link :href="route('chat.index')" title="Chat" class="text-gray-500 hover:text-indigo-600 p-2 rounded-full transition-colors">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3.68-3.091a1.256 1.256 0 00-.86-.317H7.812a2.25 2.25 0 01-2.25-2.25V6.982c0-1.242 1.008-2.25 2.25-2.25h8.574a2.25 2.25 0 012.25 2.25v1.529z" />
                            </svg>
                        </Link>
                        
                        <!-- Notificaciones - SIEMPRE VISIBLE PARA DEBUG -->
                        <div class="flex items-center">
                            <NotificationBell />
                        </div>
                        
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition">
                                    <img :src="authUser.profile_picture_url" class="h-8 w-8 rounded-full object-cover mr-2" :alt="authUser.name">
                                    <span>{{ authUser.name }}</span>
                                    <svg class="ml-2 -mr-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.show.public', { user: authUser.username })">Mi Perfil</DropdownLink>
                                <DropdownLink :href="route('profile.edit')">Editar Perfil</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">Cerrar Sesión</DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                    
                    <div class="flex items-center sm:hidden space-x-3 -mr-2">
                        <!-- Notificaciones Móvil -->
                        <NotificationBell />
                        
                        <Link :href="route('chat.index')" title="Chat" class="text-gray-500 hover:text-indigo-600 p-1 rounded-full">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3.68-3.091a1.256 1.256 0 00-.86-.317H7.812a2.25 2.25 0 01-2.25-2.25V6.982c0-1.242 1.008-2.25 2.25-2.25h8.574a2.25 2.25 0 012.25 2.25v1.529z" /></svg>
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
    <nav v-if="authUser" class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg md:hidden z-30">
        <div class="flex items-center justify-around h-16">
            <Link :href="route('feed.index')" title="Inicio" class="bottom-nav-link" :class="{'active': route().current('feed.index')}">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                <span class="text-xs mt-1">Inicio</span>
            </Link>
            <Link :href="route('discover.index')" title="Descubrir" class="bottom-nav-link" :class="{'active': route().current('discover.index')}">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <span class="text-xs mt-1">Descubrir</span>
            </Link>
            <Link :href="route('posts.create')" title="Nuevo Post" class="bottom-nav-link -mt-1">
                <span class="inline-block p-3 bg-indigo-600 rounded-full shadow-md"><svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg></span>
            </Link>
            <Link :href="route('connections.index')" title="Conexiones" class="bottom-nav-link relative" :class="{'active': route().current('connections.index')}">
                <span v-if="pendingRequestsCount > 0" class="notification-dot-mobile"></span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197M15 11a4 4 0 110-5.292M12 4.354a4 4 0 010 5.292" /></svg>
                <span class="text-xs mt-1">Conexiones</span>
            </Link>
            <Link :href="route('profile.show.public', { user: authUser.username })" title="Perfil" class="bottom-nav-link" :class="{'active': route().current('profile.show.public')}">
                <img :src="authUser.profile_picture_url || 'https://ui-avatars.com/api/?name=' + authUser.name" alt="Perfil" class="h-6 w-6 rounded-full object-cover">
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