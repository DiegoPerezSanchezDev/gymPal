<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useTimeAgo } from '@/composables/useTimeAgo';
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

const { timeAgo } = useTimeAgo();

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
        post_shared: 'text-blue-500 bg-blue-50 dark:bg-blue-900/20',
        new_message: 'text-indigo-500 bg-indigo-50 dark:bg-indigo-900/20',
        connection_accepted: 'text-green-500 bg-green-50 dark:bg-green-900/20',
        post_liked: 'text-red-500 bg-red-50 dark:bg-red-900/20',
        post_commented: 'text-purple-500 bg-purple-50 dark:bg-purple-900/20',
    };
    return colors[type] || colors.new_message;
};
</script>

<template>
    <div class="w-full flex flex-col max-h-[500px] bg-white dark:bg-gray-800 transition-colors">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between transition-colors">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white transition-colors">Notificaciones</h3>
            <div class="flex items-center gap-2">
                <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-black transition-colors"
                >
                    Marcar todo como leído
                </button>
                <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Lista de notificaciones -->
        <div class="overflow-y-auto flex-1">
            <div v-if="isLoading" class="p-8 text-center text-gray-500 dark:text-gray-400">
                <div class="flex justify-center mb-2">
                    <div class="w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                </div>
                Cargando...
            </div>
            <div v-else-if="localNotifications.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <p class="font-bold text-gray-900 dark:text-white mb-1">No hay nada por aquí</p>
                <p class="text-sm">Te avisaremos cuando pase algo interesante</p>
            </div>
            <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
                <button
                    v-for="notification in localNotifications"
                    :key="notification.id"
                    @click="handleNotificationClick(notification)"
                    :class="[
                        'w-full px-4 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all border-l-4',
                        !notification.read_at ? 'bg-indigo-50/30 dark:bg-indigo-900/10 border-indigo-600' : 'border-transparent'
                    ]"
                >
                    <div class="flex items-start gap-3">
                        <!-- Icono -->
                        <div :class="['flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center shadow-sm', getNotificationColor(notification.type)]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getNotificationIcon(notification.type)" />
                            </svg>
                        </div>
                        
                        <!-- Contenido -->
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <p class="text-sm font-black text-gray-900 dark:text-white truncate pr-2">{{ notification.title }}</p>
                                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase whitespace-nowrap">{{ timeAgo(notification.created_at) }}</p>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-0.5 line-clamp-2">{{ notification.message }}</p>
                        </div>
                    </div>
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div v-if="localNotifications.length > 0" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 text-center transition-colors">
            <Link :href="route('notifications.index')" class="text-xs font-black text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 uppercase tracking-widest transition-colors">
                Ver todo
            </Link>
        </div>
    </div>
</template>

