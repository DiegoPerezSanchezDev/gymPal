<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import Dropdown from './Dropdown.vue';
import NotificationsDropdown from './NotificationsDropdown.vue';

const unreadCount = ref(0);
const dropdownOpen = ref(false);

// Cargar contador de notificaciones no leídas
const loadUnreadCount = async () => {
    try {
        const response = await axios.get(route('notifications.unread-count'));
        unreadCount.value = response.data.count || 0;
    } catch (error) {
        console.error('Error al cargar contador de notificaciones:', error);
        unreadCount.value = 0;
    }
};

// Cargar al montar y cada 30 segundos
let interval = null;
onMounted(() => {
    loadUnreadCount();
    interval = setInterval(loadUnreadCount, 30000);
});

onUnmounted(() => {
    if (interval) {
        clearInterval(interval);
    }
});

const handleNotificationClicked = (notification) => {
    if (!notification.read_at) {
        unreadCount.value = Math.max(0, unreadCount.value - 1);
    }
};

const handleMarkAllRead = () => {
    unreadCount.value = 0;
};
</script>

<template>
<<<<<<< HEAD
    <Link
        :href="route('notifications.index')"
        class="relative p-2 min-w-[44px] min-h-[44px] flex items-center justify-center text-gray-500 hover:text-gray-700 focus:outline-none transition-colors"
        title="Notificaciones"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        
        <!-- Badge de notificaciones no leídas -->
        <span
            v-if="unreadCount > 0"
            class="absolute top-0 right-0 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white border-2 border-white dark:border-gray-800 shadow-sm"
        >
            {{ unreadCount > 99 ? '99+' : unreadCount }}
        </span>
    </Link>
=======
    <div class="relative flex items-center">
        <Dropdown align="right" width="w-80 sm:w-[450px]">
            <template #trigger>
                <button
                    class="relative p-2 min-w-[44px] min-h-[44px] flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 focus:outline-none transition-all duration-200 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
                    title="Notificaciones"
                    @click="loadUnreadCount"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    
                    <!-- Badge de notificaciones no leídas -->
                    <span
                        v-if="unreadCount > 0"
                        class="absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-black text-white border-2 border-white dark:border-gray-800 shadow-sm animate-bounce"
                    >
                        {{ unreadCount > 9 ? '9+' : unreadCount }}
                    </span>
                </button>
            </template>

            <template #content>
                <div class="w-80 sm:w-[450px] overflow-hidden rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
                    <NotificationsDropdown 
                        :unreadCount="unreadCount"
                        @close="dropdownOpen = false"
                        @mark-all-read="handleMarkAllRead"
                        @notification-clicked="handleNotificationClicked"
                    />
                </div>
            </template>
        </Dropdown>
    </div>
>>>>>>> master
</template>

<style scoped>
/* Evitar que el dropdown se cierre al hacer click dentro si queremos persistencia extra (opcional) */
</style>


