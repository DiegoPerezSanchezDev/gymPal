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
                router.visit(route('posts.show', { post: data.post_id, from: 'notifications' }));
            }
            break;
        case 'new_message':
            if (data.sender_username) {
                router.visit(route('chat.show', { user: data.sender_username, from: 'notifications' }));
            }
            break;
        case 'connection_accepted':
            if (data.accepter_username) {
                router.visit(route('profile.show.public', { user: data.accepter_username, from: 'notifications' }));
            }
            break;
        case 'workout_saved':
        case 'workout_cloned':
            if (data.workout_id) {
                router.visit(route('workouts.show', { workout: data.workout_id, from: 'notifications' }));
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
        <div class="min-h-screen bg-gray-50/50 py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Header Section -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Notificaciones</h1>
                        <p class="text-gray-500 mt-1 text-sm">Mantente al día con tu actividad en GymPal</p>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <button
                            v-if="notifications.data.length > 0"
                            @click="deleteAll"
                            :disabled="isDeleting"
                            class="group flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 transition-all disabled:opacity-50"
                        >
                            <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            {{ isDeleting ? 'Limpiando...' : 'Limpiar todo' }}
                        </button>
                        <button
                            v-if="unread_count > 0"
                            @click="markAllAsRead"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-indigo-600 bg-white border border-indigo-100 shadow-sm hover:shadow hover:border-indigo-200 transition-all"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Marcar leídas
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-1.5 mb-6 inline-flex">
                    <button
                        @click="currentFilter = 'all'"
                        :class="[
                            'px-6 py-2 rounded-xl text-sm font-bold transition-all duration-200',
                            currentFilter === 'all' 
                                ? 'bg-gray-900 text-white shadow-md' 
                                : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50'
                        ]"
                    >
                        Todas
                    </button>
                    <button
                        @click="currentFilter = 'unread'"
                        :class="[
                            'px-6 py-2 rounded-xl text-sm font-bold transition-all duration-200 flex items-center gap-2',
                            currentFilter === 'unread' 
                                ? 'bg-indigo-600 text-white shadow-md' 
                                : 'text-gray-500 hover:text-indigo-600 hover:bg-indigo-50'
                        ]"
                    >
                        No leídas
                        <span v-if="unread_count > 0" :class="currentFilter === 'unread' ? 'bg-white/20 text-white' : 'bg-indigo-100 text-indigo-600'" class="py-0.5 px-2 rounded-full text-xs">
                            {{ unread_count }}
                        </span>
                    </button>
                </div>

                <!-- Notifications List -->
                <div v-if="notifications && notifications.data && notifications.data.length > 0" class="space-y-3">
                    <TransitionGroup 
                        enter-active-class="transition duration-300 ease-out"
                        enter-from-class="transform translate-y-4 opacity-0"
                        enter-to-class="transform translate-y-0 opacity-100"
                        leave-active-class="transition duration-200 ease-in"
                        leave-from-class="transform translate-y-0 opacity-100"
                        leave-to-class="transform translate-y-4 opacity-0"
                    >
                        <div
                            v-for="notification in notifications.data"
                            :key="notification.id"
                            @click="handleNotificationClick(notification)"
                            class="group relative bg-white rounded-2xl p-4 border transition-all duration-200 cursor-pointer overflow-hidden"
                            :class="[
                                !notification.read_at 
                                    ? 'border-indigo-100 shadow-md hover:shadow-lg hover:border-indigo-200 bg-gradient-to-r from-white to-indigo-50/30' 
                                    : 'border-gray-100 shadow-sm hover:shadow-md hover:border-gray-200'
                            ]"
                        >
                            <!-- Unread Indicator Dot -->
                            <div v-if="!notification.read_at" class="absolute top-4 right-4">
                                <span class="flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
                                </span>
                            </div>

                            <div class="flex items-start gap-4">
                                <!-- Icon Box -->
                                <div :class="['flex-shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform duration-200', getNotificationColor(notification.type)]">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getNotificationIcon(notification.type)" />
                                    </svg>
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1 min-w-0 pt-0.5">
                                    <div class="flex flex-col">
                                        <p class="text-base font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                            {{ notification.title }}
                                        </p>
                                        <p class="text-sm text-gray-600 mt-1 leading-relaxed">
                                            {{ notification.message }}
                                        </p>
                                        <p class="text-xs font-medium text-gray-400 mt-2 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            {{ formatDate(notification.created_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </TransitionGroup>

                    <!-- Pagination -->
                    <div v-if="notifications.links && notifications.links.length > 3" class="mt-8 flex justify-center">
                        <div class="flex flex-wrap justify-center gap-2 bg-white p-2 rounded-xl shadow-sm border border-gray-100">
                            <Link
                                v-for="(link, key) in notifications.links"
                                :key="key"
                                :href="link.url"
                                :preserve-scroll="true"
                                :preserve-state="true"
                                :data="{ unread_only: currentFilter === 'unread' ? 1 : 0 }"
                                class="px-3 py-1.5 text-sm font-medium rounded-lg transition-all"
                                :class="[
                                    link.active 
                                        ? 'bg-indigo-600 text-white shadow-md' 
                                        : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-3xl border border-dashed border-gray-200">
                    <div class="w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center mb-6 animate-pulse">
                        <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">
                        {{ currentFilter === 'unread' ? '¡Estás al día!' : 'Sin notificaciones' }}
                    </h3>
                    <p class="text-gray-500 max-w-sm mx-auto mb-8">
                        {{ currentFilter === 'unread' 
                            ? 'No tienes notificaciones sin leer. ¡Buen trabajo!' 
                            : 'Aquí aparecerán tus interacciones, likes y nuevos mensajes.' 
                        }}
                    </p>
                    <button 
                        v-if="currentFilter === 'unread'"
                        @click="currentFilter = 'all'"
                        class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm"
                    >
                        Ver historial completo
                    </button>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

