// resources/js/Components/PostCard.vue
<script setup>
import { defineProps, ref } from "vue";
import { Link } from "@inertiajs/vue3"; // Si necesitas enlaces dentro de la tarjeta

const props = defineProps({
    post: {
        type: Object,
        required: true,
        default: () => ({
            // Valores por defecto para que no rompa si algo falta
            id: null,
            user_avatar: "https://via.placeholder.com/40",
            user_name: "Usuario GymPal",
            user_username: "gympaluser", // Asumiendo que tendrás un username para el enlace al perfil
            created_at_human: "Hace un momento",
            content:
            "¡Un gran día para entrenar y conectar con nuevos GymPals!",
            image_url: null,
            likes_count: 0,
            comments_count: 0,
            is_liked_by_user: false, // Para saber si el usuario actual le dio like
        }),
    },
});

const isLiked = ref(props.post.is_liked_by_user);
const localLikesCount = ref(props.post.likes_count);

const toggleLike = async () => {
    // Aquí iría la lógica para enviar la petición de like/unlike al backend
    // Ejemplo (necesitarás una ruta y controlador para esto):
    // try {
    //     const response = await axios.post(route('posts.like', props.post.id));
    //     isLiked.value = response.data.is_liked;
    //     localLikesCount.value = response.data.likes_count;
    // } catch (error) {
    //     console.error('Error al dar like:', error);
    //     // Revertir el estado visual si falla
    //     isLiked.value = !isLiked.value;
    //     localLikesCount.value += isLiked.value ? 1 : -1;
    // }

    // Simulación por ahora:
    isLiked.value = !isLiked.value;
    localLikesCount.value += isLiked.value ? 1 : -1;
    console.log(`Post ${props.post.id} liked: ${isLiked.value}`);
};

const openComments = () => {
    // Lógica para abrir/mostrar comentarios o navegar a una vista de comentarios
    console.log(`Abrir comentarios para el post ${props.post.id}`);
    // Podrías emitir un evento o usar Inertia.visit si los comentarios son una página separada
    // Inertia.visit(route('posts.show', props.post.id));
};
</script>

<template>
    <div class="bg-white shadow-md rounded-lg p-4">
        <div class="flex items-center mb-3">
            <Link
                :href="
                    route('profile.show.public', { user: post.user_username })
                "
            >
                <img
                    :src="post.user_avatar"
                    alt="User Avatar"
                    class="w-10 h-10 rounded-full mr-3 hover:opacity-80 transition-opacity"
                />
            </Link>
            <div>
                <Link
                    :href="
                        route('profile.show.public', {
                            user: post.user_username,
                        })
                    "
                    class="font-semibold text-gray-900 hover:underline"
                >
                    {{ post.user_name }}
                </Link>
                <p class="text-xs text-gray-500">{{ post.created_at_human }}</p>
            </div>
            <!-- Puedes añadir un menú de opciones del post aquí (ej: ... tres puntos) -->
        </div>

        <p class="text-gray-700 mb-3 whitespace-pre-line">{{ post.content }}</p>

        <img
            v-if="post.image_url"
            :src="post.image_url"
            alt="Post Image"
            class="rounded-lg w-full max-h-[70vh] object-contain mb-3 cursor-pointer"
            @click="() => console.log('Abrir imagen en modal')"
        />
        <div class="flex justify-start space-x-4 text-gray-500">
            <button
                @click="toggleLike"
                class="flex items-center focus:outline-none group"
                :class="
                    isLiked
                        ? 'text-red-500'
                        : 'text-gray-500 hover:text-red-500'
                "
            >
                <svg
                    class="w-5 h-5 mr-1 transition-transform duration-150 ease-in-out"
                    :class="{
                        'transform scale-125 fill-red-500': isLiked,
                        'group-hover:scale-110': !isLiked,
                    }"
                    :fill="isLiked ? 'currentColor' : 'none'"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                    ></path>
                </svg>
                <span>{{ localLikesCount }}</span>
            </button>
            <button
                @click="openComments"
                class="flex items-center text-gray-500 hover:text-blue-500 focus:outline-none group"
            >
                <svg
                    class="w-5 h-5 mr-1 transition-transform duration-150 ease-in-out group-hover:scale-110"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                    ></path>
                </svg>
                <span>{{ post.comments_count }}</span>
            </button>
            <!-- Puedes añadir un botón de Compartir aquí -->
        </div>
    </div>
</template>

<style scoped>
/* Estilos específicos si los necesitas */
.whitespace-pre-line {
    white-space: pre-line; /* Para que los saltos de línea en el contenido se respeten */
}
</style>
