<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import axios from 'axios';
import SharedPostCard from '@/Components/SharedPostCard.vue';

// Props que vienen del controlador
const props = defineProps({
    title: String,
    chatWithUser: Object,
    conversationId: Number,
    messages: Object, // Objeto de paginación de Laravel
});

const page = usePage();
const authUserId = computed(() => page.props.auth.user.id);
const newMessage = ref('');
const messagesContainer = ref(null);
const isSending = ref(false);
const localMessages = ref([]);

// Inicializar mensajes locales desde las props
onMounted(() => {
    if (props.messages && props.messages.data) {
        localMessages.value = [...props.messages.data].reverse();
    }
    scrollToBottom();
});

// Función para hacer scroll al final
const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

// Enviar mensaje
const sendMessage = async () => {
    if (!newMessage.value.trim() || isSending.value) return;
    
    isSending.value = true;
    const messageText = newMessage.value.trim();
    newMessage.value = ''; // Limpiar inmediatamente para mejor UX
    
    try {
        const response = await axios.post(
            route('chat.messages.store', { conversation: props.conversationId }),
            { body: messageText }
        );
        
        // Agregar el nuevo mensaje a la lista local
        localMessages.value.push(response.data);
        scrollToBottom();
    } catch (error) {
        console.error('Error al enviar mensaje:', error);
        newMessage.value = messageText; // Restaurar el texto si hay error
        alert('Error al enviar el mensaje. Inténtalo de nuevo.');
    } finally {
        isSending.value = false;
    }
};

// Verificar si un mensaje es un post compartido
const isSharedPost = (message) => {
    return message.type === 'shared_post';
};

// Observar cambios en los mensajes de las props
watch(() => props.messages, (newMessages) => {
    if (newMessages && newMessages.data) {
        localMessages.value = [...newMessages.data].reverse();
        scrollToBottom();
    }
}, { deep: true });

</script>

<template>
    <Head :title="title || `Chat con ${chatWithUser.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ title || `Chat con ${chatWithUser.name}` }}
            </h2>
        </template>

        <div class="py-2 md:py-6">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 h-[calc(100vh-150px)] flex flex-col">
                <!-- Cabecera del Chat (nombre del otro usuario) -->
                <div class="bg-white shadow-sm sm:rounded-t-lg p-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <img v-if="chatWithUser.profile_picture_url" :src="chatWithUser.profile_picture_url" :alt="chatWithUser.name" class="w-10 h-10 rounded-full mr-3 object-cover">
                        <img v-else :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(chatWithUser.name)}&background=random&color=fff`" :alt="chatWithUser.name" class="w-10 h-10 rounded-full mr-3">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ chatWithUser.name }}</h3>
                            <p class="text-xs text-gray-500">@{{ chatWithUser.username }}</p>
                        </div>
                    </div>
                </div>

                <!-- Contenedor de Mensajes -->
                <div ref="messagesContainer" class="flex-grow bg-gray-50 p-4 overflow-y-auto space-y-4">
                    <div v-if="localMessages.length > 0">
                        <div 
                            v-for="message in localMessages" 
                            :key="message.id"
                            :class="['flex mb-4', message.user_id === authUserId ? 'justify-end' : 'justify-start']"
                        >
                            <!-- Mensaje de texto normal -->
                            <div 
                                v-if="!isSharedPost(message)"
                                :class="[
                                    'max-w-xs lg:max-w-md px-4 py-2 rounded-lg shadow-sm',
                                    message.user_id === authUserId 
                                        ? 'bg-indigo-500 text-white rounded-br-none' 
                                        : 'bg-white text-gray-800 rounded-bl-none'
                                ]"
                            >
                                <p class="text-sm whitespace-pre-wrap">{{ message.body }}</p>
                                <p 
                                    class="text-xs mt-1" 
                                    :class="message.user_id === authUserId ? 'text-indigo-200 text-right' : 'text-gray-400 text-left'"
                                >
                                    {{ new Date(message.created_at).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }) }}
                                </p>
                            </div>

                            <!-- Post compartido -->
                            <div 
                                v-else
                                :class="['max-w-md lg:max-w-lg', message.user_id === authUserId ? 'text-right' : 'text-left']"
                            >
                                <div class="mb-1">
                                    <p 
                                        class="text-xs mb-2"
                                        :class="message.user_id === authUserId ? 'text-indigo-600 text-right' : 'text-gray-500 text-left'"
                                    >
                                        {{ message.body }}
                                    </p>
                                </div>
                                <SharedPostCard :postData="message.metadata || {}" />
                                <p 
                                    class="text-xs mt-1"
                                    :class="message.user_id === authUserId ? 'text-indigo-200 text-right' : 'text-gray-400 text-left'"
                                >
                                    {{ new Date(message.created_at).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center text-gray-500 py-10">
                        <p>No hay mensajes en esta conversación todavía.</p>
                        <p>¡Sé el primero en enviar uno!</p>
                    </div>
                </div>

                <!-- Input para Nuevo Mensaje -->
                <div class="bg-white p-4 border-t border-gray-200 sm:rounded-b-lg">
                    <form @submit.prevent="sendMessage" class="flex items-center space-x-3">
                        <input
                            type="text"
                            v-model="newMessage"
                            placeholder="Escribe un mensaje..."
                            class="flex-grow border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            autocomplete="off"
                        />
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                            :disabled="!newMessage.trim() || isSending"
                        >
                            {{ isSending ? 'Enviando...' : 'Enviar' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>