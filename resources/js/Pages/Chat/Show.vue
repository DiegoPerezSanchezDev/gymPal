<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
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
const imageFile = ref(null);
const imagePreview = ref(null);
const fileInputRef = ref(null);

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

// Manejar selección de imagen
const handleImageSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        imageFile.value = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

// Remover imagen seleccionada
const removeImage = () => {
    imageFile.value = null;
    imagePreview.value = null;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

// Enviar mensaje
const sendMessage = async () => {
    if ((!newMessage.value.trim() && !imageFile.value) || isSending.value) return;
    
    isSending.value = true;
    const messageText = newMessage.value.trim();
    newMessage.value = ''; // Limpiar inmediatamente para mejor UX
    
    try {
        const formData = new FormData();
        if (messageText) {
            formData.append('body', messageText);
        }
        if (imageFile.value) {
            formData.append('image', imageFile.value);
        }
        
        const response = await axios.post(
            route('chat.messages.store', { conversation: props.conversationId }),
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }
        );
        
        // Agregar el nuevo mensaje a la lista local
        localMessages.value.push(response.data);
        removeImage(); // Limpiar imagen después de enviar
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
        <div class="flex flex-col h-[calc(100vh-65px)] bg-gradient-to-b from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 transition-colors">
            
            <!-- Header del Chat (Sticky) -->
            <div class="bg-white dark:bg-gray-800 shadow-md border-b border-gray-200 dark:border-gray-700 px-4 py-4 flex items-center justify-between sticky top-0 z-10 transition-colors">
                <div class="flex items-center gap-3">
                    <Link :href="route('chat.index')" class="text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all p-2 rounded-full hover:bg-indigo-50 dark:hover:bg-indigo-900/30">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    
                    <Link :href="route('profile.show.public', { user: chatWithUser.username })" class="flex items-center gap-3 group">
                        <div class="relative">
                            <img v-if="chatWithUser.profile_picture_url" :src="`/storage/${chatWithUser.profile_picture_url}`" :alt="chatWithUser.name" class="w-11 h-11 rounded-full object-cover border-2 border-indigo-100 dark:border-indigo-800 group-hover:border-indigo-300 dark:group-hover:border-indigo-500 transition-all shadow-sm">
                            <img v-else :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(chatWithUser.name)}&background=random&color=fff&size=128`" :alt="chatWithUser.name" class="w-11 h-11 rounded-full border-2 border-indigo-100 dark:border-indigo-800 group-hover:border-indigo-300 dark:group-hover:border-indigo-500 transition-all shadow-sm">
                            <!-- Indicador de estado online -->
                            <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ chatWithUser.name }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 transition-colors">@{{ chatWithUser.username }}</p>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Área de Mensajes -->
            <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4 scroll-smooth">
                <div v-if="localMessages.length > 0" class="space-y-4">
                    <div 
                        v-for="(message, index) in localMessages" 
                        :key="message.id"
                        class="flex w-full"
                        :class="message.user_id === authUserId ? 'justify-end' : 'justify-start'"
                    >
                        <div class="flex flex-col max-w-[85%] md:max-w-[70%]">
                            <!-- Mensaje de Texto/Imagen -->
                            <div 
                                v-if="!isSharedPost(message)"
                                class="shadow-md text-sm relative group"
                                :class="[
                                    message.user_id === authUserId 
                                        ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl rounded-tr-md' 
                                        : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600 rounded-2xl rounded-tl-md transition-colors',
                                    message.image_url ? '' : 'px-4 py-2.5'
                                ]"
                            >
                                <!-- Imagen si existe -->
                                <div v-if="message.image_url" class="rounded-2xl overflow-hidden">
                                    <img :src="`/storage/${message.image_url}`" class="w-full max-w-sm object-cover cursor-pointer hover:opacity-90 transition-opacity" @click="() => window.open(`/storage/${message.image_url}`, '_blank')">
                                </div>
                                
                                <!-- Texto si existe -->
                                <p v-if="message.body" class="whitespace-pre-wrap leading-relaxed" :class="message.image_url ? 'px-4 py-2.5' : ''">{{ message.body }}</p>
                                
                                <!-- Hora -->
                                <div class="text-[10px] mt-1.5 opacity-70 flex justify-end gap-1 items-center" :class="message.image_url ? 'px-4 pb-2' : ''">
                                    <span>{{ new Date(message.created_at).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }) }}</span>
                                    <span v-if="message.user_id === authUserId">
                                        <!-- Check doble -->
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                    </span>
                                </div>
                            </div>

                            <!-- Post Compartido -->
                            <div v-else class="w-full">
                                <div v-if="message.body" class="mb-2 text-xs text-gray-500 dark:text-gray-400 font-medium transition-colors" :class="message.user_id === authUserId ? 'text-right' : 'text-left'">
                                    {{ message.body }}
                                </div>
                                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 shadow-md bg-white dark:bg-gray-800 transition-colors">
                                    <SharedPostCard :postData="message.metadata || {}" />
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1.5 text-right">
                                    {{ new Date(message.created_at).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Estado Vacío -->
                <div v-else class="h-full flex flex-col items-center justify-center text-center p-8">
                    <div class="w-24 h-24 bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-full flex items-center justify-center mb-4 shadow-inner transition-colors">
                        <svg class="w-12 h-12 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <p class="text-gray-900 dark:text-white font-bold text-lg transition-colors">¡Saluda a {{ chatWithUser.name }}!</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 transition-colors">Comienza la conversación 💬</p>
                </div>
            </div>

            <!-- Input Flotante -->
            <div class="bg-white dark:bg-gray-800 p-4 border-t border-gray-200 dark:border-gray-700 shadow-lg transition-colors">
                <!-- Preview de imagen -->
                <div v-if="imagePreview" class="mb-3 relative inline-block">
                    <img :src="imagePreview" class="h-20 w-20 object-cover rounded-lg border-2 border-indigo-200">
                    <button @click="removeImage" type="button" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-all shadow-md">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                
                <form @submit.prevent="sendMessage" class="flex items-end gap-3 max-w-4xl mx-auto">
                    <input type="file" ref="fileInputRef" @change="handleImageSelect" accept="image/*" class="hidden">
                    
                    <button @click="fileInputRef.click()" type="button" class="p-2.5 text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-all rounded-full">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </button>
                    
                    <div class="flex-1 relative">
                        <input
                            type="text"
                            v-model="newMessage"
                            placeholder="Escribe un mensaje..."
                            class="w-full border-0 bg-gray-100 dark:bg-gray-700 rounded-full px-5 py-3 focus:ring-2 focus:ring-indigo-500 focus:bg-white dark:focus:bg-gray-600 text-gray-900 dark:text-white dark:placeholder-gray-400 transition-all shadow-inner font-medium"
                            autocomplete="off"
                        />
                    </div>
                    
                    <button
                        type="submit"
                        class="p-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-full shadow-lg hover:shadow-xl hover:scale-105 transition-all disabled:opacity-50 disabled:hover:scale-100 flex-shrink-0 flex items-center justify-center"
                        :disabled="(!newMessage.trim() && !imageFile) || isSending"
                    >
                        <svg v-if="!isSending" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        <svg v-else class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>