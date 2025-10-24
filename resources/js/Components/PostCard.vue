// resources/js/Components/PostCard.vue
<script setup>
import { computed, ref, onMounted, watch } from "vue";
import { Link } from "@inertiajs/vue3";
import axios from 'axios';

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
});

// Propiedad computada para formatear la fecha de forma legible
const formattedDate = computed(() => {
    return new Date(props.post.created_at).toLocaleString('es-ES', {
        day: '2-digit', month: 'long', hour: '2-digit', minute: '2-digit'
    });
});

const isLiked = ref(false);
const localLikesCount = ref(0);
const isProcessingLike = ref(false);

// Función para inicializar valores
const initializeValues = () => {
    isLiked.value = props.post.is_liked || false;
    localLikesCount.value = props.post.likes_count || 0;
};

// Inicializar valores cuando el componente se monta
onMounted(() => {
    initializeValues();
});

// Reaccionar a cambios en las props
watch(() => props.post, () => {
    initializeValues();
}, { deep: true });

const toggleLike = async () => {
    if (isProcessingLike.value) return;
    isProcessingLike.value = true;
    const originalIsLiked = isLiked.value;
    
    isLiked.value = !isLiked.value;
    localLikesCount.value += isLiked.value ? 1 : -1;

    try {
        const response = await axios.post(route('posts.like.toggle', props.post.id));
        isLiked.value = response.data.is_liked;
        localLikesCount.value = response.data.likes_count;

    } catch (error) {
        console.error("Error al dar like:", error);
        isLiked.value = originalIsLiked;
        localLikesCount.value += originalIsLiked ? 1 : -1;
    } finally {
        isProcessingLike.value = false;
    }
};

const openComments = () => {
    // Lógica para abrir/mostrar comentarios o navegar a una vista de comentarios
    console.log(`Abrir comentarios para el post ${props.post.id}`);
    // Podrías emitir un evento o usar Inertia.visit si los comentarios son una página separada
    // Inertia.visit(route('posts.show', props.post.id));
};
</script>

<template>
    <div class="bg-white shadow-lg rounded-xl max-w-xl mx-auto border border-gray-200/80">
        
        <!-- ================== Encabezado del Post ================== -->
        <div class="p-4 flex items-center justify-between">
            <div class="flex items-center">
                <Link :href="route('profile.show.public', { user: post.user.username })">
                    <img
                        :src="post.user.profile_picture_url || 'https://ui-avatars.com/api/?name=' + post.user.name + '&background=random'"
                        alt="Avatar del usuario"
                        class="w-11 h-11 rounded-full object-cover ring-2 ring-gray-100"
                    />
                </Link>
                <div class="ml-3">
                    <Link :href="route('profile.show.public', { user: post.user.username })" class="font-bold text-sm text-gray-800 hover:underline">
                        {{ post.user.name }}
                    </Link>
                    <p class="text-xs text-gray-500">{{ formattedDate }}</p>
                </div>
            </div>
            <button class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" /></svg>
            </button>
        </div>

        <!-- ================== Contenido del Post ================== -->
        <p v-if="post.content" class="px-5 pb-4 text-gray-700 whitespace-pre-line text-[15px] leading-relaxed">
            {{ post.content }}
        </p>

        <div v-if="post.image_path" class="bg-gray-100 max-h-[70vh] overflow-hidden">
            <img :src="'/storage/' + post.image_path" alt="Imagen de la publicación" class="w-full h-full object-cover"/>
        </div>
        
        <!-- ================== Acciones del Post ================== -->
        <div class="px-2 py-1 flex justify-around items-center border-t border-gray-100">
            
            <button 
                @click="toggleLike" 
                :disabled="isProcessingLike"
                class="action-button group disabled:opacity-70 disabled:cursor-not-allowed" 
                :class="{ 'text-red-500': isLiked, 'text-gray-500 hover:text-red-500': !isLiked }"
            >
                <svg class="w-7 h-7 transition-transform" :class="{'transform scale-110 fill-red-500': isLiked, 'group-hover:scale-110': !isLiked}" :fill="isLiked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.5l1.318-1.182a4.5 4.5 0 116.364 6.364L12 21l-7.682-7.682a4.5 4.5 0 010-6.364z"></path></svg>
                <span class="text-sm font-semibold ml-1">{{ localLikesCount }}</span>
            </button>
            
            <button class="action-button group text-gray-500 hover:text-indigo-500">
                <svg class="w-7 h-7 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <span class="text-sm font-semibold ml-1">{{ post.comments_count || 0 }}</span>
            </button>
            
            <button class="action-button group text-gray-500 hover:text-green-500">
                <svg class="w-7 h-7 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342A8.963 8.963 0 018 12.001c0-1.01.198-1.968.563-2.835m7.899 5.578A8.963 8.963 0 0116 12.001c0-1.01.198-1.968.563-2.835m0 5.67a8.965 8.965 0 01-7.899 0m7.899 0l-1.42 1.42m-5.058-8.54l1.42-1.42" /></svg>
                <span class="text-sm font-semibold ml-1">Compartir</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
/* Estilos específicos si los necesitas */
.whitespace-pre-line {
    white-space: pre-line; 
}
.action-button {
    @apply flex items-center justify-center w-full px-3 py-2 rounded-lg transition-colors duration-200 focus:outline-none hover:bg-gray-100;
}
</style>
