<!-- Página de prueba para verificar notificaciones -->
<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const notifications = ref([]);
const unreadCount = ref(0);
const isLoading = ref(false);

const loadNotifications = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(route('notifications.index'), {
            headers: { 'Accept': 'application/json' }
        });
        notifications.value = response.data.data || [];
        unreadCount.value = response.data.unread_count || 0;
    } catch (error) {
        console.error('Error:', error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    loadNotifications();
});
</script>

<template>
    <Head title="Prueba de Notificaciones" />
    
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h1 class="text-2xl font-bold mb-4">Prueba de Notificaciones</h1>
                    
                    <div class="mb-4">
                        <p class="text-gray-600">
                            Notificaciones no leídas: <strong class="text-red-600">{{ unreadCount }}</strong>
                        </p>
                        <p class="text-sm text-gray-500 mt-2">
                            Si ves el icono de campana en el navbar, debería mostrar este número.
                        </p>
                    </div>

                    <div v-if="isLoading" class="text-gray-500">Cargando...</div>
                    
                    <div v-else-if="notifications.length === 0" class="text-gray-500">
                        No hay notificaciones. Ejecuta: <code class="bg-gray-100 px-2 py-1 rounded">php artisan db:seed --class=NotificationSeeder</code>
                    </div>
                    
                    <div v-else class="space-y-4">
                        <div
                            v-for="notification in notifications"
                            :key="notification.id"
                            :class="[
                                'p-4 border rounded-lg',
                                notification.read_at ? 'bg-gray-50 border-gray-200' : 'bg-blue-50 border-blue-200'
                            ]"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900">{{ notification.title }}</h3>
                                    <p class="text-gray-600 mt-1">{{ notification.message }}</p>
                                    <p class="text-xs text-gray-400 mt-2">
                                        {{ new Date(notification.created_at).toLocaleString('es-ES') }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Tipo: {{ notification.type }} | 
                                        {{ notification.read_at ? 'Leída' : 'No leída' }}
                                    </p>
                                </div>
                                <span v-if="!notification.read_at" class="ml-2">
                                    <span class="h-3 w-3 bg-blue-500 rounded-full inline-block"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

