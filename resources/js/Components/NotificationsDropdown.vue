<!-- Componente dropdown para mostrar notificaciones -->
<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    notifications: {
        type: Array,
        default: () => [],
    },
    unreadCount: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(['close', 'mark-all-read', 'notification-clicked']);

const isLoading = ref(false);
const localNotifications = ref([...props.notifications]);

// Cargar notificaciones al montar
onMounted(async () => {
    await loadNotifications();
});

const loadNotifications = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(route('notifications.index'), {
            params: { unread_only: false },
            headers: {
                'Accept': 'application/json',
            },
        });
        // Si es una respuesta paginada de Laravel
        if (response.data.data) {
            localNotifications.value = response.data.data;
        } else if (response.data.notifications) {
            localNotifications.value = response.data.notifications;
        } else {
            localNotifications.value = [];
        }
    } catch (error) {
        console.error('Error al cargar notificaciones:', error);
        localNotifications.value = [];
    } finally {
        isLoading.value = false;
    }
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Ahora';
    if (diffMins < 60) return `Hace ${diffMins} min`;
    if (diffHours < 24) return `Hace ${diffHours} h`;
    if (diffDays < 7) return `Hace ${diffDays} d`;
    return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
};

const markAsRead = async (notification) => {
    if (notification.read_at) return; // Ya está leída

    try {
        await axios.patch(route('notifications.mark-as-read', { notification: notification.id }));
        notification.read_at = new Date().toISOString();
        emit('notification-clicked', notification);
    } catch (error) {
        console.error('Error al marcar notificación como leída:', error);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.patch(route('notifications.mark-all-as-read'));
        localNotifications.value.forEach(n => {
            n.read_at = new Date().toISOString();
        });
        emit('mark-all-read');
    } catch (error) {
        console.error('Error al marcar todas como leídas:', error);
    }
};

const handleNotificationClick = (notification) => {
    markAsRead(notification);
    
    // Navegar según el tipo de notificación
    const data = notification.data || {};
    
    switch (notification.type) {
        case 'post_shared':
        case 'post_liked':
        case 'post_commented':
            if (data.post_id) {
                window.location.href = route('posts.show', data.post_id);
            }
            break;
        case 'new_message':
            if (data.sender_username) {
                window.location.href = route('chat.show', { user: data.sender_username });
            }
            break;
        case 'connection_accepted':
            if (data.accepter_username) {
                window.location.href = route('profile.show.public', { user: data.accepter_username });
            }
            break;
    }
    
    emit('close');
};

const getNotificationIcon = (type) => {
    const icons = {
        post_shared: 'M8.684 13.342A8.963 8.963 0 018 12.001c0-1.01.198-1.968.563-2.835m7.899 5.578A8.963 8.963 0 0116 12.001c0-1.01.198-1.968.563-2.835m0 5.67a8.965 8.965 0 01-7.899 0m7.899 0l-1.42 1.42m-5.058-8.54l1.42-1.42',
        new_message: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        connection_accepted: 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
        post_liked: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
        post_commented: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    };
    return icons[type] || icons.new_message;
};

const getNotificationColor = (type) => {
    const colors = {
        post_shared: 'text-blue-500 bg-blue-50',
        new_message: 'text-indigo-500 bg-indigo-50',
        connection_accepted: 'text-green-500 bg-green-50',
        post_liked: 'text-red-500 bg-red-50',
        post_commented: 'text-purple-500 bg-purple-50',
    };
    return colors[type] || colors.new_message;
};
</script>

<template>
    <div class="w-full flex flex-col max-h-[500px]">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Notificaciones</h3>
            <div class="flex items-center gap-2">
                <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium"
                >
                    Marcar todas como leídas
                </button>
                <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Lista de notificaciones -->
        <div class="overflow-y-auto flex-1">
            <div v-if="isLoading" class="p-8 text-center text-gray-500">
                Cargando notificaciones...
            </div>
            <div v-else-if="localNotifications.length === 0" class="p-8 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <p class="font-medium">No tienes notificaciones</p>
                <p class="text-sm">Las notificaciones aparecerán aquí</p>
            </div>
            <div v-else class="divide-y divide-gray-100">
                <button
                    v-for="notification in localNotifications"
                    :key="notification.id"
                    @click="handleNotificationClick(notification)"
                    :class="[
                        'w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors',
                        !notification.read_at ? 'bg-indigo-50/50' : ''
                    ]"
                >
                    <div class="flex items-start gap-3">
                        <!-- Icono -->
                        <div :class="['flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center', getNotificationColor(notification.type)]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getNotificationIcon(notification.type)" />
                            </svg>
                        </div>
                        
                        <!-- Contenido -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900">{{ notification.title }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ notification.message }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ formatDate(notification.created_at) }}</p>
                        </div>
                        
                        <!-- Indicador de no leída -->
                        <div v-if="!notification.read_at" class="flex-shrink-0">
                            <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                        </div>
                    </div>
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div v-if="localNotifications.length > 0" class="px-4 py-3 border-t border-gray-200 text-center">
            <Link :href="route('notifications.index')" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                Ver todas las notificaciones
            </Link>
        </div>
    </div>
</template>

