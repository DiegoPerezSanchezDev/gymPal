<!-- Componente SIMPLE para el icono de notificaciones en el navbar -->
<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const unreadCount = ref(0);

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
</script>

<template>
    <Link
        :href="route('notifications.index')"
        class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none transition-colors"
        title="Notificaciones"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        
        <!-- Badge de notificaciones no leídas -->
        <span
            v-if="unreadCount > 0"
            class="absolute top-0 right-0 block h-5 w-5 text-xs font-semibold text-white bg-red-500 rounded-full flex items-center justify-center"
        >
            {{ unreadCount > 99 ? '99+' : unreadCount }}
        </span>
    </Link>
</template>

