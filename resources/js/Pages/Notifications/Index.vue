<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    notifications: Object,
    unread_count: Number,
    filters: Object, // Recibimos los filtros actuales
});

const currentFilter = ref(new URLSearchParams(window.location.search).get('unread_only') === '1' ? 'unread' : 'all');
const isDeleting = ref(false);

// Observar cambios en el filtro para recargar
watch(currentFilter, (val) => {
    router.get(route('notifications.index'), 
        { unread_only: val === 'unread' ? 1 : 0 }, 
        { preserveState: true, preserveScroll: true, only: ['notifications', 'unread_count'] }
    );
});

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
    if (notification.read_at) return;
    
    try {
        await axios.patch(route('notifications.mark-as-read', { notification: notification.id }));
        // Actualización optimista local si es necesario, pero Inertia recargará
        router.reload({ only: ['notifications', 'unread_count'] });
    } catch (error) {
        console.error('Error:', error);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.patch(route('notifications.mark-all-as-read'));
        router.reload({ only: ['notifications', 'unread_count'] });
    } catch (error) {
        console.error('Error:', error);
    }
};

const deleteAll = async () => {
    if (!confirm('¿Estás seguro de que quieres eliminar todas las notificaciones? Esta acción no se puede deshacer.')) return;
    
    isDeleting.value = true;
    try {
        await axios.delete(route('notifications.delete-all'));
        router.reload({ only: ['notifications', 'unread_count'] });
    } catch (error) {
        console.error('Error:', error);
    } finally {
        isDeleting.value = false;
    }
};

const handleNotificationClick = (notification) => {
    markAsRead(notification);
    
    const data = notification.data || {};
    
    switch (notification.type) {
        case 'post_shared':
        case 'post_liked':
        case 'post_commented':
            if (data.post_id) {
                router.visit(route('posts.show', data.post_id));
            }
            break;
        case 'new_message':
            if (data.sender_username) {
                router.visit(route('chat.show', { user: data.sender_username }));
            }
            break;
        case 'connection_accepted':
            if (data.accepter_username) {
                router.visit(route('profile.show.public', { user: data.accepter_username }));
            }
            break;
    }
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
    <Head title="Notificaciones" />
    
    <AuthenticatedLayout>
        <div class="py-6 sm:py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Header con Tabs -->
                    <div class="border-b border-gray-200">
                        <div class="px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <h1 class="text-2xl font-bold text-gray-900">Notificaciones</h1>
                            
                            <div class="flex items-center gap-2 self-end sm:self-auto">
                                <button
                                    v-if="notifications.data.length > 0"
                                    @click="deleteAll"
                                    :disabled="isDeleting"
                                    class="text-sm text-red-600 hover:text-red-800 font-medium px-3 py-1 rounded hover:bg-red-50 transition-colors disabled:opacity-50"
                                >
                                    {{ isDeleting ? 'Eliminando...' : 'Borrar todas' }}
                                </button>
                                <button
                                    v-if="unread_count > 0"
                                    @click="markAllAsRead"
                                    class="text-sm text-indigo-600 hover:text-indigo-800 font-medium px-3 py-1 rounded hover:bg-indigo-50 transition-colors"
                                >
                                    Marcar todas leídas
                                </button>
                            </div>
                        </div>
                        
                        <!-- Tabs de navegación -->
                        <div class="px-4 sm:px-6 flex space-x-6">
                            <button
                                @click="currentFilter = 'all'"
                                :class="[
                                    'pb-3 text-sm font-medium border-b-2 transition-colors',
                                    currentFilter === 'all' 
                                        ? 'border-indigo-500 text-indigo-600' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Todas
                            </button>
                            <button
                                @click="currentFilter = 'unread'"
                                :class="[
                                    'pb-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2',
                                    currentFilter === 'unread' 
                                        ? 'border-indigo-500 text-indigo-600' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                No leídas
                                <span v-if="unread_count > 0" class="bg-indigo-100 text-indigo-600 py-0.5 px-2 rounded-full text-xs">
                                    {{ unread_count }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Lista de notificaciones -->
                    <div v-if="notifications && notifications.data && notifications.data.length > 0" class="divide-y divide-gray-200 min-h-[300px]">
                        <TransitionGroup 
                            enter-active-class="transition duration-300 ease-out"
                            enter-from-class="transform translate-x-5 opacity-0"
                            enter-to-class="transform translate-x-0 opacity-100"
                            leave-active-class="transition duration-200 ease-in"
                            leave-from-class="transform translate-x-0 opacity-100"
                            leave-to-class="transform translate-x-5 opacity-0"
                        >
                            <button
                                v-for="notification in notifications.data"
                                :key="notification.id"
                                @click="handleNotificationClick(notification)"
                                :class="[
                                    'w-full px-4 sm:px-6 py-4 text-left hover:bg-gray-50 transition-colors block',
                                    !notification.read_at ? 'bg-indigo-50/40' : 'bg-white'
                                ]"
                            >
                                <div class="flex items-start gap-4">
                                    <!-- Icono -->
                                    <div :class="['flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center', getNotificationColor(notification.type)]">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getNotificationIcon(notification.type)" />
                                        </svg>
                                    </div>
                                    
                                    <!-- Contenido -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1 pr-4">
                                                <p class="text-sm sm:text-base font-semibold text-gray-900 line-clamp-1">{{ notification.title }}</p>
                                                <p class="text-sm text-gray-600 mt-0.5 line-clamp-2">{{ notification.message }}</p>
                                                <p class="text-xs text-gray-400 mt-1.5">{{ formatDate(notification.created_at) }}</p>
                                            </div>
                                            <div v-if="!notification.read_at" class="flex-shrink-0 mt-1.5">
                                                <div class="w-2.5 h-2.5 bg-indigo-500 rounded-full ring-2 ring-white"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </TransitionGroup>
                    </div>

                    <!-- Estado vacío -->
                    <div v-else class="p-12 text-center flex flex-col items-center justify-center min-h-[400px]">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">
                            {{ currentFilter === 'unread' ? 'Estás al día' : 'Sin notificaciones' }}
                        </h3>
                        <p class="text-sm text-gray-500 max-w-xs mx-auto">
                            {{ currentFilter === 'unread' 
                                ? 'No tienes notificaciones sin leer en este momento.' 
                                : 'Las notificaciones sobre likes, comentarios y conexiones aparecerán aquí.' 
                            }}
                        </p>
                        <button 
                            v-if="currentFilter === 'unread'"
                            @click="currentFilter = 'all'"
                            class="mt-4 text-indigo-600 hover:text-indigo-800 text-sm font-medium"
                        >
                            Ver todas las notificaciones
                        </button>
                    </div>

                    <!-- Paginación -->
                    <div v-if="notifications && notifications.links && notifications.links.length > 3" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="text-sm text-gray-700 text-center sm:text-left">
                                Mostrando {{ notifications.from }} a {{ notifications.to }} de {{ notifications.total }} resultados
                            </div>
                            <div class="flex flex-wrap justify-center gap-1">
                                <Link
                                    v-for="(link, key) in notifications.links"
                                    :key="key"
                                    :href="link.url"
                                    :preserve-scroll="true"
                                    :preserve-state="true"
                                    :data="{ unread_only: currentFilter === 'unread' ? 1 : 0 }"
                                    :class="[
                                        'px-3 py-1 text-sm rounded-md transition-colors',
                                        link.active 
                                            ? 'bg-indigo-600 text-white shadow-sm' 
                                            : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300',
                                        !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                    ]"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

