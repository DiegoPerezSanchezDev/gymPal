<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // O tu layout principal
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    ref,                // Para crear variables reactivas.
    reactive,           // Para crear objetos reactivos.
    computed,           // Para crear propiedades computadas.
    watch,              // Para observar cambios en datos reactivos.
    onMounted,          // Hook del ciclo de vida: se ejecuta después de que el componente se monta.
    onUpdated,          // Hook del ciclo de vida: se ejecuta después de que el componente se actualiza.
    onUnmounted,        // Hook del ciclo de vida: se ejecuta antes de que el componente se desmonte.
} from 'vue';

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
const messagesContainer = ref(null); // Para auto-scroll

// Aquí iría la lógica para enviar mensajes, cargar más mensajes, etc.
const sendMessage = () => {
    if (!newMessage.value.trim()) return;
    // Lógica para enviar el mensaje usando axios.post a la ruta 'chat.messages.store'
    // ...
    console.log('Enviando mensaje:', newMessage.value, 'a la conversación:', props.conversationId);
    // Ejemplo de cómo se vería con Ziggy y axios
    // axios.post(route('chat.messages.store', { conversation: props.conversationId }), { body: newMessage.value })
    //   .then(response => {
    //     // Añadir mensaje a la lista local, limpiar input, etc.
    //     newMessage.value = '';
    //   })
    //   .catch(error => {
    //     console.error('Error al enviar mensaje:', error);
    //   });
    newMessage.value = ''; // Limpiar por ahora
};

onMounted(() => {
    // Lógica al montar, como hacer scroll al final de los mensajes
    // o iniciar la escucha de eventos de broadcasting si lo implementas.
    console.log('Página de chat individual montada.');
    console.log('Chat con:', props.chatWithUser);
    console.log('ID Conversación:', props.conversationId);
    console.log('Mensajes iniciales:', props.messages);
});

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
                    <!-- Iterar sobre los mensajes (hay que invertir el orden si vienen de latest()) -->
                    <div v-if="messages && messages.data && messages.data.length > 0">
                        <!-- Los mensajes vienen paginados y 'latest()', así que el más nuevo está primero. -->
                        <!-- Para mostrarlos en orden cronológico, podrías invertir el array 'data' o manejarlo en el scroll -->
                        <div v-for="message in [...messages.data].reverse()" :key="message.id"
                            :class="['flex', message.user_id === $page.props.auth.user.id ? 'justify-end' : 'justify-start']">
                            <div :class="['max-w-xs lg:max-w-md px-3 py-2 rounded-lg shadow', message.user_id === $page.props.auth.user.id ? 'bg-indigo-500 text-white' : 'bg-white text-gray-800']">
                                <p class="text-sm">{{ message.body }}</p>
                                <p class="text-xs mt-1" :class="message.user_id === $page.props.auth.user.id ? 'text-indigo-200 text-right' : 'text-gray-400 text-left'">
                                    {{ new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
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
                            :disabled="!newMessage.trim()"
                        >
                            Enviar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>