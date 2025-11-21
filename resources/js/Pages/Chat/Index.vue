<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    title: String,
    conversations: Object, // Objeto de paginación de Laravel
});

// Un helper computado para verificar si hay conversaciones
const hasConversations = computed(() => {
    return (
        props.conversations &&
        props.conversations.data &&
        props.conversations.data.length > 0
    );
});

// Helper para obtener la URL del avatar
const getAvatarUrl = (convo) => {
    if (convo.chat_avatar && convo.chat_avatar.startsWith('http')) {
        return convo.chat_avatar;
    }
    if (convo.chat_avatar && convo.chat_avatar.startsWith('profile-pictures/')) {
        return `/storage/${convo.chat_avatar}`;
    }
    return convo.chat_avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(convo.chat_title)}&background=random&color=fff&size=128`;
};
</script>

<template>
    <Head :title="title || 'Mis Chats'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-extrabold text-xl text-gray-900 leading-tight">
                        GymPal Chats
                    </h2>
                    <p class="text-xs text-gray-500 font-medium">Tus conversaciones</p>
                </div>
            </div>
        </template>

        <div class="py-6 md:py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Estado vacío -->
                <div v-if="!hasConversations" class="bg-white rounded-2xl shadow-xl border border-gray-100 p-12">
                    <div class="flex flex-col items-center justify-center text-center">
                        <div class="w-24 h-24 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mb-6 shadow-inner">
                            <svg class="w-12 h-12 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">¡Aún no tienes conversaciones!</h3>
                        <p class="text-gray-500 max-w-md mb-6">Conecta con otros GymPals en la sección Descubrir para empezar a chatear y encontrar compañeros de entrenamiento.</p>
                        <Link :href="route('discover.index')" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-full shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                            Ir a Descubrir
                        </Link>
                    </div>
                </div>

                <!-- Lista de conversaciones -->
                <div v-else class="space-y-3">
                    <Link 
                        v-for="convo in conversations.data" 
                        :key="convo.id"
                        :href="convo.other_user_username ? route('chat.show', { user: convo.other_user_username }) : '#'"
                        class="block bg-white rounded-2xl shadow-md hover:shadow-xl border border-gray-100 transition-all duration-300 overflow-hidden group hover:scale-[1.02] transform"
                        :class="{ 'pointer-events-none opacity-50': !convo.other_user_username }"
                    >
                        <div class="p-4 flex items-center gap-4">
                            <!-- Avatar con indicador online -->
                            <div class="relative flex-shrink-0">
                                <img 
                                    :src="getAvatarUrl(convo)" 
                                    :alt="convo.chat_title"
                                    class="w-16 h-16 rounded-full object-cover border-2 border-indigo-100 group-hover:border-indigo-300 transition-all shadow-sm"
                                >
                                <!-- Indicador de mensajes no leídos -->
                                <span v-if="convo.unread_count > 0" class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold rounded-full flex items-center justify-center shadow-lg animate-pulse">
                                    {{ convo.unread_count > 9 ? '9+' : convo.unread_count }}
                                </span>
                            </div>

                            <!-- Contenido -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors truncate text-base">
                                        {{ convo.chat_title }}
                                    </h3>
                                    <span class="text-xs text-gray-400 font-medium flex-shrink-0 ml-2">
                                        {{ convo.last_message_at_human }}
                                    </span>
                                </div>
                                
                                <p class="text-sm text-gray-600 truncate" :class="{ 'font-semibold text-gray-900': convo.unread_count > 0 }">
                                    <span v-if="convo.last_message_sender === 'Tú'" class="text-indigo-500 font-medium">Tú: </span>
                                    <span v-if="convo.last_message_body">{{ convo.last_message_body }}</span>
                                    <span v-else class="italic text-gray-400">📷 Imagen</span>
                                </p>
                            </div>

                            <!-- Chevron -->
                            <div class="flex-shrink-0 text-gray-300 group-hover:text-indigo-500 transition-colors">
                                <svg class="w-6 h-6 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Paginación -->
                <div v-if="hasConversations && conversations.links && conversations.links.length > 3" class="mt-8 flex justify-center gap-2">
                    <template v-for="(link, key) in conversations.links" :key="key">
                        <div 
                            v-if="link.url === null" 
                            class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed" 
                            v-html="link.label" 
                        />
                        <Link 
                            v-else 
                            class="px-4 py-2 text-sm font-medium rounded-lg transition-all shadow-sm hover:shadow-md" 
                            :class="link.active ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'" 
                            :href="link.url" 
                            v-html="link.label" 
                            preserve-scroll 
                        />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
